<?php

namespace app\admin\controller\Sys;

use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use think\exception\ValidateException;


class MethodCodeVisitor extends NodeVisitorAbstract
{
    public $code = '';
    
    private $classes = [];
    
    private $methods = [];
    
    public $space = '';
    
    private $useDeclarations = [];
    
    private $properties = [];
    
    public $routes = [];
    
    public $traits = [];
    
    public function __construct($code) {
        $this->code = $code;
    }
    
    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_) {
            $className = $node->name->toString();
            $extends = $node->extends ? $node->extends->toString() : null;
            
            $this->classes[$className] = $extends;
        }
        
        if ($node instanceof Node\Stmt\ClassMethod) {
            $methodName = $node->name->name;
            $methodCode = $this->getMethodCode($node);
            $methodComments = $this->getMethodComments($node);
            $this->methods[$methodName] = [
                'funcname' => $methodName,
                'code' => $methodCode,
                'doc' => $methodComments,
            ];
        }
        
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->space = $node->name->toString();
        }
        
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $alias = $use->alias ? $use->alias->name : null;
                $this->useDeclarations[] = [
                    'use' => $use->name->toString(),
                    'alias' => $alias,
                ];
            }
        }
        
        if ($node instanceof Node\Stmt\Property) {
            $this->properties[] = [
                'code' => $this->getPropertiesCode($node),
                'doc' => $this->getPropertyComments($node),
                'funcname' => $node->props[0]->name->toString(),
            ];
        }
        
        if ($node instanceof Node\Stmt\TraitUse) {
            foreach ($node->traits as $trait) {
                $this->traits[] = $trait->toString();
            }
        }
        
        if ($node instanceof Node\Expr\MethodCall
            && $node->name instanceof Node\Identifier
            && $node->name->name === 'rule'
        ) {
            $args = $node->args;
            print_r($args);
        }
    }
    
    public function getClasses() {
        return $this->classes;
    }
    
    public function getTraits() {
        return $this->traits;
    }
    
    public function getProperties() {
        return $this->properties;
    }
    
    public function getNamespace() {
        return $this->space;
    }
    
    public function getUseDeclarations() {
        return $this->useDeclarations;
    }
    
    public function getRoute() {
        return $this->routes;
    }
    
    private function getMethodCode(Node\Stmt\ClassMethod $method) {
        $methodCode = $this->code;
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        
        if (is_string($methodCode)) {
            $methodCode = explode("\n", $methodCode);
        }
        $code = '';
        $methodCode = array_slice($methodCode, $startLine - 1, $endLine - $startLine + 1);
        $code .= implode("\n", $methodCode);
        
        return $code;
    }
    
    private function getPropertiesCode(Node\Stmt\Property $method) {
        $methodCode = $this->code;
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        
        if (is_string($methodCode)) {
            $methodCode = explode("\n", $methodCode);
        }
        $code = '';
        $methodCode = array_slice($methodCode, $startLine - 1, $endLine - $startLine + 1);
        $code .= implode("\n", $methodCode);
        
        
        return $code;
    }
    
    
    private function getMethodComments(Node\Stmt\ClassMethod $method) {
        $comments = [];
        $commentsBeforeMethod = $method->getAttribute('comments');
        if ($commentsBeforeMethod) {
            foreach ($commentsBeforeMethod as $comment) {
                $comments[] = $comment->getText();
            }
        }
        return $comments;
    }
    
    private function getPropertyComments(Node\Stmt\Property $method) {
        $comments = [];
        $commentsBeforeMethod = $method->getAttribute('comments');
        if ($commentsBeforeMethod) {
            foreach ($commentsBeforeMethod as $comment) {
                $comments[] = $comment->getText();
            }
        }
        return $comments;
    }
    
    public function getMethods() {
        return $this->methods;
    }
}

class Build
{
    
    function create($code, $filepath) {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        
        $targetStmts = $parser->parse($code . "\n}");
        
        $traverser = new NodeTraverser;
        $target = new MethodCodeVisitor($code);
        $traverser->addVisitor($target);
        $traverser->traverse($targetStmts);
        
        
        $localcode = "";
        if (file_exists($filepath)) {
            $localcode = file_get_contents($filepath);
            $localcode = removeEsFromContent($localcode);
        }
        
        $localStmts = $parser->parse($localcode);
        
        $localTraverser = new NodeTraverser;
        $local = new MethodCodeVisitor($localcode);
        $localTraverser->addVisitor($local);
        $localTraverser->traverse($localStmts);
        
        $targetUser = $target->getUseDeclarations();
        $use = [];
        foreach ($targetUser as $k => $v) {
            $use[] = $v['use'];
        }
        foreach ($local->getUseDeclarations() as $k => $v) {
            if (!in_array($v['use'], $use)) {
                array_push($targetUser, $v);
            }
        }
        
        $targetTraits = $target->getTraits();
        $traits = [];
        foreach ($targetTraits as $k => $v) {
            $traits[] = $v;
        }
        foreach ($local->getTraits() as $k => $v) {
            if (!in_array($v, $traits)) {
                array_push($targetTraits, $v);
            }
        }
        
        $targetMethods = $target->getMethods();
        $func = [];
        foreach ($targetMethods as $k => $v) {
            $func[] = $v['funcname'];
        }
        foreach ($local->getMethods() as $k => $v) {
            $doc = $this->getBuildStatus(current($v["doc"]));
            if ($doc == "true") {
                if (in_array($v['funcname'], $func)) {
                    array_push($targetMethods, $v);
                }
            } else {
                array_push($targetMethods, $v);
            }
        }
        
        $filtered = [];
        $seen = [];
        
        foreach ($targetMethods as $item) {
            $funcname = $item['funcname'];
            $doc = $this->getBuildStatus(current($item["doc"]));
            if (array_key_exists($funcname, $seen)) {
                $index = $seen[$funcname];
                if ($doc == "false") {
                    $filtered[$index] = $item;
                }
            } else {
                $seen[$funcname] = count($filtered);
                $filtered[] = $item;
            }
        }
        
        $targetProperties = $target->getProperties();
        foreach ($local->getProperties() as $k => $v) {
            array_push($targetProperties, $v);
        }
        
        $filteredProperties = [];
        $seenProperties = [];
        
        foreach ($targetProperties as $item) {
            $funcname = $item['funcname'];
            $doc = $this->getBuildStatus(current($item["doc"]));
            if (array_key_exists($funcname, $seenProperties)) {
                $index = $seenProperties[$funcname];
                if ($doc == "false") {
                    $filteredProperties[$index] = $item;
                }
            } else {
                $seenProperties[$funcname] = count($filteredProperties);
                $filteredProperties[] = $item;
            }
        }
        
        $str = "";
        $str .= "<?php\n";
        $str .= sprintf("namespace %s;\n", $target->getNamespace());
        foreach ($targetUser as $v) {
            if ($v['alias']) {
                $str .= sprintf("use %s as %s;\n", $v['use'], $v['alias']);
            } else {
                $str .= sprintf("use %s;\n", $v['use']);
            }
        }
        $str .= "\n";
        foreach ($target->getClasses() as $k => $v) {
            if ($v) {
                $str .= sprintf("class %s extends %s{\n", $k, $v);
            } else {
                $str .= sprintf("class %s{\n", $k);
            }
        }
        $str .= "\n";
        
        foreach ($targetTraits as $v) {
            $str .= sprintf("	use %s;\n", $v);
        }
        
        foreach ($filteredProperties as $k => $v) {
            if (current($v['doc'])) {
                $str .= sprintf("	%s\n%s\n\n", current($v['doc']), $v['code']);
            } else {
                $str .= sprintf("	%s\n%s\n", current($v['doc']), $v['code']);
            }
            
        }
        $str .= "\n\n";
        
        foreach ($filtered as $k => $v) {
            $str .= sprintf("	%s\n%s\n\n\n", current($v['doc']), $v['code']);
        }
        $str .= "}";
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (false === file_put_contents($filepath, $str)) {
            throw new ValidateException('cache write error');
        }
    }
    
    
    function createRouter($code, $filepath) {
        $routes = [];
        $pattern = "/Route::rule\((['\"])(.*?)\\1.*?\);.*?(\/\/.*)?$/m";
        preg_match_all($pattern, $code, $target_matches);
        $str = "";
        $str .= "<?php\n";
        $str .= "use think\\facade\Route;\n\n";
        
        $target = $target_matches[0];
        
        $code = '';
        
        if (file_exists($filepath)) {
            $code = file_get_contents($filepath);
        }
        
        preg_match_all($pattern, $code, $local_matches);
        foreach ($local_matches[0] as $v) {
            if ($v && !in_array($v, $target)) {
                array_push($target, $v);
            }
        }
        
        foreach ($target as $k => $v) {
            $str .= $v . "\n";
        }
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (false === file_put_contents($filepath, $str)) {
            throw new ValidateException('cache write error');
        }
    }
    
    function getBuildStatus($doc) {
        if (count(explode("@buildcode(", $doc)) > 1) {
            $data = explode(")", explode("@buildcode(", $doc)[1]);
            return trim($data[0]);
        } else {
            return "false";
        }
    }
    
}

