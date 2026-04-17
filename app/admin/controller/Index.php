<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Session;
use think\response\Json;

class Index extends Admin
{
    
    public function index()
    {
        //   dump(session());
        return view('index');
    }
    
    public function ai_chat()
    {
        //   dump(session());
        return view('ai_chat');
    }
    
    
    //后台首页主体内容
    public function main()
    {
        if (!$this->request->isPost()) {
            $info = db('secrect')->column('data', 'name');
            return view('main', $info);
        } else {
            //折线图数据
            $echat_data['day_count'] = [
                'title' => '当月业绩折线图',
                'day' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30],    //每月天数
                'data' => [0, 0, 0, 0, 0, 126, 246, 452, 45, 36, 479, 588, 434, 9, 18, 27, 18, 88, 45, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]    //每天数据
            ];
            
            if (config('my.show_home_chats', true)) {
                $data['echat_data'] = $echat_data;
            }
            $data['card_data'] = $this->getCardData();
            $data['menus'] = $this->getMenuLink();
            $data['status'] = 200;
            return json($data);
        }
    }
    
    
    //首页统计数据
    private function getCardData()
    {
        $card_data = [    //头部统计数据
            [
                'title_icon' => "el-icon-user",
                'card_title' => "访问",
                'card_cycle' => "年",
                'card_cycle_back_color' => "#409EFF",
                'bottom_title' => "访问总量",
                'vist_num' => rand(0, 100000),
                'vist_all_num' => rand(0, 100000),
                'vist_all_icon' => "el-icon-trophy",
            ],
            [
                'title_icon' => "el-icon-download",
                'card_title' => "下载",
                'card_cycle' => "月",
                'card_cycle_back_color' => "#67C23A",
                'bottom_title' => "下载总量",
                'vist_num' => rand(0, 100000),
                'vist_all_num' => rand(0, 100000),
                'vist_all_icon' => "el-icon-download",
            ],
            [
                'title_icon' => "el-icon-wallet",
                'card_title' => "收入",
                'card_cycle' => "日",
                'card_cycle_back_color' => "#F56C6C",
                'bottom_title' => "总收入",
                'vist_num' => rand(0, 100000),
                'vist_all_num' => rand(0, 100000),
                'vist_all_icon' => "el-icon-coin",
            ],
            [
                'title_icon' => "el-icon-coordinate",
                'card_title' => "用户",
                'card_cycle' => "月",
                'card_cycle_back_color' => "#E6A23C",
                'bottom_title' => "总用户",
                'vist_num' => rand(0, 100000),
                'vist_all_num' => rand(0, 100000),
                'vist_all_icon' => "el-icon-data-line",
            ],
        ];
        
        return $card_data;
    }
    
    
    //获取首页快捷导航
    private function getMenuLink()
    {
        if (config('my.show_home_menu', true)) {
            $data = Db::name('menu')->field('title,menu_pic,controller_name,url')->where('app_id', 1)->where('home_show', 1)->limit(15)->select()->toArray();
            foreach ($data as $k => $v) {
                if (!$v['url']) {
                    $data[$k]['url'] = (string)url('admin/' . str_replace('/', '.', $v['controller_name']) . '/index');
                } else {
                    $data[$k]['url'] = $v['url'];
                }
            }
            return $data;
        }
    }
    
    /**
     * 添加日程到数据库
     * @param string $content 日程内容
     * @param string $date 日期 (格式: Y-m-d)
     * @return array 操作结果
     */
    public function addSchedule()
    {
        
        if (!$this->request->isPost()) {
            return json(['status' => 0, 'msg' => '非法请求']);
        }
        
        $content = $this->request->post('content');
        $date = $this->request->post('date');
        $rc_time = $this->request->post('rc_time');
        
        if (empty($content) || empty($date)) {
            return json(['status' => 0, 'msg' => '日程内容和日期不能为空']);
        }
        
        try {
            // 转换日期为时间戳
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                return json(['status' => 0, 'msg' => '日期格式不正确']);
            }
            
            // 获取当前用户信息
            $yuangong_id = Session::get('admin.yuangong_id');
            $bumen_id = Session::get('admin.bumen_id');
            
            
            // dump($yuangong_id);
            // dump($bumen_id);
            // exit;
            
            // 插入数据库
            $data = [
                'yuangong_id' => $yuangong_id,
                'rc_date' => $timestamp,
                'rc_daiban' => $content,
                'rc_zhuantai' => 1, // 1表示未完成
                'bumen_id' => $bumen_id,
                'rc_time' => $rc_time,
                'rc_chuangjian' => time()
            ];
            
            $result = Db::name('richeng')->insert($data);
            
            if ($result) {
                return json(['status' => 1, 'msg' => '日程添加成功']);
            } else {
                return json(['status' => 0, 'msg' => '日程添加失败']);
            }
        } catch (\Exception $e) {
            return json(['status' => 0, 'msg' => '系统错误: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 从数据库删除日程
     * @param int $schedule_id 日程ID
     * @return array 操作结果
     */
    public function deleteSchedule()
    {
        if (!$this->request->isPost()) {
            return json(['status' => 0, 'msg' => '非法请求']);
        }
        
        $schedule_id = $this->request->post('schedule_id');
        
        if (empty($schedule_id)) {
            return json(['status' => 0, 'msg' => '请选择要删除的日程']);
        }
        
        try {
            // 获取当前用户ID用于验证权限
            $yuangong_id = Session::get('admin.yuangong_id');
            
            // 删除前验证日程是否属于当前用户
            $schedule = Db::name('richeng')
                ->where('richeng_id', $schedule_id)
                ->where('yuangong_id', $yuangong_id)
                ->find();
            
            if (!$schedule) {
                return json(['status' => 0, 'msg' => '无权删除此日程或日程不存在']);
            }
            
            $result = Db::name('richeng')
                ->where('richeng_id', $schedule_id)
                ->delete();
            
            if ($result) {
                return json(['status' => 1, 'msg' => '日程删除成功']);
            } else {
                return json(['status' => 0, 'msg' => '日程删除失败']);
            }
        } catch (\Exception $e) {
            return json(['status' => 0, 'msg' => '系统错误: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取用户日程列表
     * @param string $month 月份 (格式: Y-m)
     * @return Json 日程列表
     */
    public function getSchedules()
    {
        if (!$this->request->isPost()) {
            return json(['status' => 0, 'msg' => '非法请求']);
        }
        
        try {
            // 获取当前用户ID - 确保与添加日程时使用相同的session键
            $yuangong_id = Session::get('admin.yuangong_id');
            
            if (empty($yuangong_id)) {
                return json(['status' => 0, 'msg' => '用户信息获取失败']);
            }
            
            $whereTime = [];
            $month = $this->request->post('month');
            if (!empty($month)) {
                // 计算月份的开始和结束时间戳
                $start_time = strtotime($month . '-01 00:00:00');
                $end_time = strtotime('+1 month', $start_time) - 1;
                
                if ($start_time === false || $end_time === false) {
                    return json(['status' => 0, 'msg' => '日期格式不正确']);
                }
                $whereTime[] = ['rc_date', '>=', $start_time];
                $whereTime[] = ['rc_date', '<=', $end_time];
            }
            
            // 查询数据库
            $schedules = Db::name('richeng')
                ->where('yuangong_id', $yuangong_id)
//                ->whereBetween('rc_date', [$start_time, $end_time])
                ->where($whereTime)
                ->order('rc_date,richeng_id', 'asc')
                ->select()
                ->toArray();
            
            
            // 格式化返回数据
            $result = [];
            foreach ($schedules as $schedule) {
                $date = date('Y-m-d', $schedule['rc_date']);
                $result[] = [
                    'id' => $schedule['richeng_id'],
                    'date' => $date,  // 确保包含日期字段
                    'content' => date('H:i', strtotime($schedule['rc_time'])) . ' ' . $schedule['rc_daiban'],
                    'status' => $schedule['rc_zhuantai'],
                    'create_time' => date('Y-m-d H:i:s', $schedule['rc_chuangjian'])
                ];
            }
            
            // 查询代办和未读消息
            $daiban = Db::name('richeng')->where(['yuangong_id' => $yuangong_id, 'rc_zhuantai' => 1])->count();
            $yidu = Db::name('yidu')->where(['yuangong_id' => $yuangong_id, 'yd_lanmu' => 'gonggaotongzhi'])->column('yd_id');
            $weidu = Db::name('gonggaotongzhi')->whereNotIn('gonggaotongzhi_id', $yidu)->count();
            return json([
                'status' => 1,
                'data' => $result,
                'month' => $month,  // 返回请求的月份以便前端验证
                'daiban' => $daiban,  // 返回请求的月份以便前端验证
                'weidu' => $weidu  // 返回请求的月份以便前端验证
            ]);
        } catch (\Exception $e) {
            return json(['status' => 0, 'msg' => '系统错误: ' . $e->getMessage()]);
        }
    }
    
    
    // 辅助方法：获取操作类型
    private function getActionType($url)
    {
        if (strpos($url, 'update') !== false) return '修改了';
        if (strpos($url, 'delete') !== false) return '删除了';
        if (strpos($url, 'add') !== false) return '添加了';
        if (strpos($url, 'Login') !== false) return '';
        return '操作了';
    }
    
    // 辅助方法：获取对象ID
    private function getObjectId($url)
    {
        if (preg_match('/\/(\d+)\//', $url, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/id=(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        return '';
    }
    
    
    private function getModuleName($url)
    {
        // 移除域名部分
        $path = parse_url($url, PHP_URL_PATH);
        
        // 匹配路径中的模块名（排除已知路由关键词）
        if (preg_match('/\/admin\/([^\/\?]+)(?:\/|$)/', $path, $matches)) {
            $moduleKey = $matches[1];
            
            // 排除通用路由（如index/login等）
            $ignoreList = ['Nimingxin', 'Liuzhuan'];
            if (in_array($moduleKey, $ignoreList)) {
                return '操作任务';
            }
            
            if ($moduleKey == 'Login') {
                return '登录了系统';
            }
            
            
            if ($moduleKey == 'logout') {
                return '退出了登录';
            }
            
            // 查询菜单表（优先匹配controller_name，其次匹配url）
            $menu = Db::name('menu')
                ->where(function ($query) use ($moduleKey) {
                    $query->where('controller_name', $moduleKey)
                        ->whereOr('url', 'like', '%' . $moduleKey . '%');
                })
                ->field('title')
                ->find();
            
            return $menu ? $menu['title'] : $moduleKey;
        }
        
        return '系统';
    }
    
    
    public function getInitData()
    {
        $data['logs'] = $this->logs();
        $yuangong_id = session('admin.yuangong_id');
        $data['qytz'] = Db::name('gonggaotongzhi')->where('ggtz_leixing', 77)
            ->withAttr('ggtz_fabushijian', function ($value, $data) {
                return date('Y-m-d H:i', $value);
            })
            ->order('gonggaotongzhi_id', 'desc')
            ->limit(5)
            ->select()->toArray();
        foreach ($data['qytz'] as &$qytz) {
            $qytz['yidu'] = in_array($yuangong_id, explode(',', $qytz['gg_yidu'])) ? 1 : 0;
        }
        
        $data['qygg'] = Db::name('gonggaotongzhi')->where('ggtz_leixing', 78)
            ->withAttr('ggtz_fabushijian', function ($value, $data) {
                return date('Y-m-d H:i', $value);
            })
            ->order('gonggaotongzhi_id', 'desc')
            ->limit(5)
            ->select()->toArray();
        foreach ($data['qygg'] as &$qygg) {
            $qygg['yidu'] = in_array($yuangong_id, explode(',', $qygg['gg_yidu'])) ? 1 : 0;
        }
        
        $data['qydsj'] = Db::name('gonggaotongzhi')->where('ggtz_leixing', 79)
            ->withAttr('ggtz_fabushijian', function ($value, $data) {
                return date('Y-m-d H:i', $value);
            })
            ->order('gonggaotongzhi_id', 'desc')
            ->limit(5)
            ->select()->toArray();
        foreach ($data['qydsj'] as &$qydsj) {
            $qydsj['yidu'] = in_array($yuangong_id, explode(',', $qydsj['gg_yidu'])) ? 1 : 0;
        }
        
        
        // 原逻辑whereor查询有误
//        $data['ldjb'] = Db::name('ldjb')
//            ->where('end_at','>=',time())
//            ->where('yuangong_id', $yuangong_id)
//            ->whereOr('FIND_IN_SET(:yuangong_id, yuangong_ids)', ['yuangong_id' => $yuangong_id])
//            ->withAttr('end_at',function ($value,$data) {
//                return date('Y-m-d', $value);
//            })
//            // ->withAttr('end_at',function ($value,$data) {
//            //     return date('Y-m-d', $value);
//            // })
//            ->order('ldjb_id','desc')
//            ->limit(5)
//            ->select();
        // zzl重新修改逻辑
        $data['ldjb'] = Db::name('ldjb')
            ->where('end_at', '>=', time())
            ->whereRaw('yuangong_id = ' . $yuangong_id . ' or FIND_IN_SET(' . $yuangong_id . ', yuangong_ids) or  FIND_IN_SET('.$yuangong_id.', yuangong_idx)')
            ->withAttr('end_at', function ($value, $data) {
                return date('m-d', $value);
            })->order('ldjb_id', 'desc')
            ->select()->toArray();
        foreach ($data['ldjb'] as $k => &$v) {
            $v['ld_title'] = '【' . Db::name('yuangong')->where('yuangong_id', $v['yuangong_id'])->value('yg_xingming') . '】' . $v['ld_title'];
            $yuangong_ids = explode(',', $v['yuangong_ids']);
            $zhixing = Db::name('yuangong')->whereIn('yuangong_id', $yuangong_ids)->column('yuangong_id,yg_xingming');
            if ($v['ld_zhouqi'] == 151) {
                // 每天
                $jd_between = [strtotime(date('Y-m-d 00:00:00')), strtotime(date('Y-m-d 23:59:59'))];
            } else if ($v['ld_zhouqi'] == 152) {
                // 每周
                $jd_between = [strtotime(date('Y-m-d 00:00:00', strtotime('monday this week'))), strtotime(date('Y-m-d 23:59:59', strtotime('sunday this week')))];
            } else if ($v['ld_zhouqi'] == 153) {
                // 每月
                $jd_between = [strtotime(date('Y-m-01 00:00:00')), strtotime(date('Y-m-31 23:59:59'))];
            } else if ($v['ld_zhouqi'] == 154) {
                // 每季度
                $quarter = ceil(date('n') / 3);
                $firstDayOfQuarter = date('Y-m-d 00:00:00', mktime(0, 0, 0, $quarter * 3 - 2, 1, date('Y')));
                $lastDayOfQuarter = date('Y-m-t 23:59:59', mktime(0, 0, 0, $quarter * 3, 1, date('Y')));
                $jd_between = [strtotime($firstDayOfQuarter), strtotime($lastDayOfQuarter)];
            } else if ($v['ld_zhouqi'] == 155) {
                // 每年
                $jd_between = [strtotime(date('Y-01-01 00:00:00')), strtotime(date('Y-12-31 00:00:00'))];
            } else if ($v['ld_zhouqi'] == 156) {
                // 固定日期
                $jd_between = [$v['start_at'], $v['end_at']];
            }
            foreach ($zhixing as &$value) {
                $res = Db::name('ldjb_jd')->whereBetween('create_at', $jd_between)->where(['yuangong_id' => $value['yuangong_id'], 'ldjb_id' => $v['ldjb_id']])->find();
                $value['status'] = 0;
                if ($res) $value['status'] = 1;
            }
            $v['zhixing'] = $zhixing;
        }
        $data['wddb'] = Db::name('richeng')
            ->where('yuangong_id', '=', session('admin.yuangong_id'))
            ->where('rc_zhuantai', '<>', 2)
            ->withAttr('rc_date', function ($value, $data) {
                
                $today = strtotime('today');
                $yesterday = strtotime('yesterday');
                $tomorrow = strtotime('tomorrow');
                
                $inputDay = strtotime('today', $value);
                
                if ($inputDay == $today) {
                    return '今天';
                } elseif ($inputDay == $yesterday) {
                    return '昨天';
                } elseif ($inputDay == $tomorrow) {
                    return '明天';
                }
                
                return date('Y-m-d', $value);
            })
            ->order('richeng_id', 'desc')
            ->limit(5)
            ->select();
        
        $data['ybsx'] = Db::name('richeng')
            ->where('yuangong_id', '=', session('admin.yuangong_id'))
            ->where('rc_zhuantai', '=', 2)
            ->withAttr('rc_date', function ($value, $data) {
                
                $today = strtotime('today');
                $yesterday = strtotime('yesterday');
                $tomorrow = strtotime('tomorrow');
                
                $inputDay = strtotime('today', $value);
                
                if ($inputDay == $today) {
                    return '今天';
                } elseif ($inputDay == $yesterday) {
                    return '昨天';
                } elseif ($inputDay == $tomorrow) {
                    return '明天';
                }
                return date('Y-m-d', $value);
            })
            ->order('richeng_id', 'desc')
            ->limit(5)
            ->select();
        
        $data['jiaoban'] = Db::name('gongzuorenwu')
            ->whereNotIn('gzrw_zhuangtai', [19, 20])
            ->whereRaw('FIND_IN_SET(' . $yuangong_id . ', yuangong_id)')
            ->withAttr('gzrw_jieshushijian', function ($value, $data) {
                
                $today = strtotime('today');
                $yesterday = strtotime('yesterday');
                $tomorrow = strtotime('tomorrow');
                
                $inputDay = strtotime('today', $value);
                
                if ($inputDay == $today) {
                    return '今天';
                } elseif ($inputDay == $yesterday) {
                    return '昨天';
                } elseif ($inputDay == $tomorrow) {
                    return '明天';
                }
                return date('Y-m-d', $value);
                
            })
            ->order('gongzuorenwu_id', 'desc')
            ->limit(5)
            ->select();
        
        $data['zhixing'] = Db::name('gongzuorenwu')
            ->whereNotIn('gzrw_zhuangtai', [19, 20])
            ->whereRaw('FIND_IN_SET(' . $yuangong_id . ', yuangong_idc)')
            ->withAttr('gzrw_jieshushijian', function ($value, $data) {
                
                $today = strtotime('today');
                $yesterday = strtotime('yesterday');
                $tomorrow = strtotime('tomorrow');
                
                $inputDay = strtotime('today', $value);
                
                if ($inputDay == $today) {
                    return '今天';
                } elseif ($inputDay == $yesterday) {
                    return '昨天';
                } elseif ($inputDay == $tomorrow) {
                    return '明天';
                }
                return date('Y-m-d', $value);
                
            })
            ->order('gongzuorenwu_id', 'desc')
            ->limit(5)
            ->select();
        
        $yuangong = Db::name('yuangong')->where('yuangong_id', session('admin.yuangong_id'))->find();
        if (in_array($yuangong['zhiwu_id'], [1, 2])) {
            // 看部门
            $yuemubiao = Db::name('mubiao')
                ->where(['yuangong_id' => $yuangong['yuangong_id'], 'mb_fanwei' => 2])
                ->where('mb_kaishiriqi', date('Y-m'))
                ->order('mubiao_id desc')->value('mb_mubiaozhi');
            $wancheng = Db::name('yeji')
                ->where(['bumen_id' => $yuangong['bumen_id'], 'yj_zhuangtai' => 2])
                ->whereBetween('yj_riqi', [strtotime(date('Y-m-01')) . ' 00:00:00', strtotime(date('Y-m-31')) . ' 23:59:59'])
                ->sum('yj_jine');
        } else {
            // 看自己
            $yuemubiao = Db::name('mubiao')
                ->where(['yuangong_id' => $yuangong['yuangong_id'], 'mb_fanwei' => 1])
                ->where('mb_kaishiriqi', date('Y-m'))
                ->order('mubiao_id desc')
                ->value('mb_mubiaozhi');
            $wancheng = Db::name('yeji')
                ->where(['yuangong_id' => $yuangong['yuangong_id'], 'yj_zhuangtai' => 2])
                ->whereBetween('yj_riqi', [strtotime(date('Y-m-01')) . ' 00:00:00', strtotime(date('Y-m-31')) . ' 23:59:59'])
                ->sum('yj_jine');
        }
        if (empty($yuemubiao) || $yuemubiao == 0 || $wancheng == 0) {
            $data['yuedu'] = 0;
        } else {
            $data['yuedu'] = round($wancheng / $yuemubiao * 100);
        }
        
        $data['chongfu'] = Db::name('chongfu')->where('yuangong_id', $yuangong['yuangong_id'])->value('chongfu_id');
        $data['chongfu'] = empty($data['chongfu']) ? 0 : $data['chongfu'];
        // 首页工作台 快捷方式 ljw
        $data['menuLink'] = self::getNoCheckMenuLink();
        
        $data['status'] = 200;
        
        return json($data);
        
    }
    
    private function logs()
    {
        $query = Db::name('log')
            ->alias('l')
            ->leftJoin('cd_admin_user u', 'l.username = u.user')
            ->field([
                'l.id',
                'u.name as operator_name',
                'l.url',
                'FROM_UNIXTIME(l.create_time) as operate_time'
            ])
            ->where('l.type', '<>', 3)
            ->order('l.id', 'desc')
            ->limit(5);
        
        $logs = $query->select()->toArray();
        
        $result = [];
        foreach ($logs as $log) {
            // 获取模块中文名
            $moduleName = $this->getModuleName($log['url']);
            
            // 解析操作类型
            $action = $this->getActionType($log['url']);
            
            // 解析操作对象ID
            $objectId = $this->getObjectId($log['url']);
            
            $result[] = [
                'operator' => $log['operator_name'],
                'action' => $action,
                'module' => $moduleName,
                'object' => $objectId ? "ID:{$objectId}" : '',
                'time' => date('m-d H:i', strtotime($log['operate_time']))
            ];
        }
        return $result;
    }
    
    public function setAdminIndexStatus()
    {
        $post = $this->request->post();
        
        if ($post['type'] == true) {
            Db::name('richeng')->where('richeng_id', $post['richeng_id'])->update(['rc_zhuantai' => 2]);
        }
        
        if ($post['type'] == false) {
            Db::name('richeng')->where('richeng_id', $post['richeng_id'])->update(['rc_zhuantai' => 1]);
        }
        
        $data['status'] = 200;
        
        return json($data);
    }
    
    
    /**
     * @return array|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @desc  无校验
     * @author    JiaWei
     * @email 975162853@qq.com
     * @date  2025/7/10
     * @time  16:56
     */
    private function getNoCheckMenuLink()
    {
//        $data = Db::name('menu')->field('title,menu_pic,controller_name,url,icon')->where('app_id', 1)->where('home_show', 1)->select()->toArray();
//         $data = Db::name('menu')->field('title,menu_pic,controller_name,url,icon')->where('app_id', 1)->where('home_show', 1)->limit(15)->select()->toArray();
//         // dump($data);die;
//         foreach ($data as $k => $v) {
//             if (!$v['url']) {
//                 $data[$k]['url'] = (string)url('admin/' . str_replace('/', '.', $v['controller_name']) . '/index');
//             } else {
//                 $data[$k]['url'] = $v['url'];
//             }
//         }
//         return $data;
//
        $nowTime = time();
        
        $link = [];
        
        $data = Db::name('menu')
            ->field('menu_id,title,menu_pic,controller_name,url,icon,table_name')
            ->where('app_id', 1)
            ->where('home_show', 1)
            ->limit(15)
            ->select()
            ->toArray();
        
        
        foreach ($data as $k => &$v) {
            // 构建URL
            if (!$v['url']) {
                $v['url'] = (string)url('admin/' . str_replace('/', '.', $v['controller_name']) . '/index');
            }
            
            // 奖励处罚
            if ($v['title'] == '奖励处罚') {
                $v['nums'] = Db::name('jianglichufa')
                    ->whereMonth('jf_riqi')
                    ->where([
                        'yuangong_id' => session('admin.yuangong_id'),
                        'jf_leixing' => 2,
                        'jf_shenhe' => 2,
                    ])
                    ->count();
            }
            
            
            // 查字段
            $fields = Db::name('field')->where('menu_id', $v['menu_id'])->whereNotNull('tx_tiaojian')->select()->toArray();
            if (!empty($fields)) {
                $query = Db::name($v['table_name']);
                
                $where = [];
                foreach ($fields as $field) {
                    $field_default = $field['tx_zhi'];
                    if ($field['tx_zhi'] == 'date()') {
                        
                        if (!empty($field['datetime_config'])) {
                            $field_default = [
                                "datetime" => date('Y-m-d H:i:s', $nowTime),
                                "date" => date('Y-m-d', $nowTime),
                                "yearmonth" => date('Y-m', $nowTime),
                                "year" => date('Y', $nowTime),
                                "month" => date('m', $nowTime),
                                "time" => date('H:i:s', $nowTime),
                            ][$field['datetime_config']];
                        } else {
                            $field_default = date('Y-m-d H:i:s', $nowTime);
                        }
                        
                        if ($field['datatype'] == 'int') $field_default = $nowTime;
                    }
                    
                    // session值
                    if (preg_match('/^session/', $field_default)) {
                        $field_default = session(trim(str_replace(["session('", "')"], "", $field_default)));
                    }
                    
                    switch ($field['tx_tiaojian']) {
                        case 1:
                            $where[] = "{$field['field']} > '{$field_default}'";
                            break;
                        case 2:
                            $where[] = "{$field['field']} < '{$field_default}'";
                            break;
                        case 3:
                            $where[] = "{$field['field']} = '{$field_default}'";
                            break;
                        case 4:
                            $where[] = "{$field['field']} >= '{$field_default}'";
                            break;
                        case 5:
                            $where[] = "{$field['field']} <= '{$field_default}'";
                            break;
                        case 6:
                            $where[] = "FIND_IN_SET('{$field_default}', {$field['field']})";
                            break;
                        case 7:
                            $where[] = "NOT FIND_IN_SET('{$field_default}', {$field['field']})";
                            break;
                        case 8:
                            $where[] = "{$field['field']} != '{$field_default}'";
                            break;
                        case 9:
                            $where[] = "{$field['field']} = '' or {$field['field']} is null";
                            break;
                        case 10:
                            $where[] = "{$field['field']} != '' and {$field['field']} is not null";
                            break;
                        default:
                            break;
                    }
                }
                
                
                if ($where) {
                    $where = implode(' and ', $where);
                    $query->whereRaw($where);
                }
                // dump($query);
                // dump($where);die;
                if (in_array('bumen_id', $fields) && in_array('yuangong_id', $fields)) {
                    if (!in_array(session('admin.role_id'), [1]) && !empty(array_intersect(session('admin.access'), ["/admin/{$v['controller_name']}/kantongziduan.html"]))) {
                        $query->whereRaw('yuangong_id = ' . session('admin.yuangong_id') . ' or bumen_id = ' . session('admin.bumen_id') . ' or FIND_IN_SET(' . session('admin.bumen_id') . ', bumen_id)');
                    }
                }
                
                if (in_array('yuangong_id', $fields) && !in_array(session('admin.role_id'), [1]) && empty(array_intersect(session('admin.access'), ["/admin/{$v['controller_name']}/kantongziduan.html", "/admin/{$v['controller_name']}/kanquanbu.html", "/admin/{$v['controller_name']}/caiwucaozuo.html"]))) {
                    $query->where('yuangong_id', session('admin.yuangong_id'));
                }
                if (in_array('bumen_id', $fields) && !in_array(session('admin.role_id'), [1]) && empty(array_intersect(session('admin.access'), ["/admin/{$v['controller_name']}/kantongziduan.html", "/admin/{$v['controller_name']}/kanquanbu.html", "/admin/{$v['controller_name']}/caiwucaozuo.html"]))) {
                    $query->where('bumen_id', session('admin.bumen_id'));
                }
                
                if (!empty($where)) {
                    $v['nums'] = $query->count();
                }
                
                // dump(Db::getLastSql());die;
            }
            
            
            // 检查权限
            if (!empty(session('admin.access')) && !in_array(session('admin.role_id'), [1])) {
                // 获取权限标识
                if (!$v['url']) {
                    // 如果没有url，使用controller_name构建权限标识
                    $authKey = '/admin/' . str_replace('.', '/', $v['controller_name']) . '/index.html';
                } else {
                    // 如果有url，直接使用url作为权限标识
                    $authKey = $v['url'];
                }
                
                
                if (is_array(session('admin.access')) && in_array($authKey, session('admin.access'))) {
                    $link[] = $v;
                }
                
            }
            if (in_array(session('admin.role_id'), [1])) {
                $link[] = $v;
            }
            
        }
        
        return $link;
        
    }
}
