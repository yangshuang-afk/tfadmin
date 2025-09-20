## TFadmin · ThinkPHP低代码框架
TfAdmin 是一款遵循 MIT 协议开源的快速开发框架，基于最新版本 ThinkPHP8 的极简后台管理系统，

项目介绍一款 PHP 语言基于ThinkPhp8、Vue、ElementUI等框架精心打造的一款模块化、插件化、高性能的前后端分离架构敏捷开发框架，可用于快速搭建前后端分离后台管理系统，本着简化开发、提升开发效率的初衷，目前框架已集成了完整的RBAC权限架构和常规基础模块，前端Vue端支持多主题切换，可以根据自己喜欢的风格选择想一个的主题，实现了个性化呈现的需求；为了敏捷快速开发，提升研发效率，框架内置了一键CRUD代码生成器，自定义了模块生成模板，包括后端PHP文件模块和前端Vue端个性化模板，可以根据已建好的表结构(字段注释需规范)快速的一键生成整个模块的所有代码和增删改查等等功能业务，真正实现了低代码开发，极大的节省了人力成本的同时提高了开发效率，缩短了研发周期，是一款真正意义上实现组件化、低代码敏捷开发框架。



内置模块

用户管理：用于维护管理系统的用户，常规信息的维护与账号设置。

角色管理：角色菜单管理与权限分配、设置角色所拥有的菜单权限。

菜单管理：配置系统菜单，操作权限，按钮权限标识等。

职级管理：主要管理用户担任的职级。

岗位管理：主要管理用户担任的岗位。

部门管理：主要管理系统组织架构，对组织架构进行统一管理维护。

操作日志：系统正常操作日志记录和查询；系统异常信息日志记录和查询。

登录日志：系统登录日志记录查询包含登录异常。

字典管理：对系统中常用的较为固定的数据进行统一维护。

配置管理：对系统的常规配置信息进行维护，网站配置管理功能进行统一维护。

城市管理：统一对全国行政区划进行维护，对其他模块提供行政区划数据支撑。

友链管理：对系统友情链接、合作伙伴等相关外链进行集成维护管理的模块。

个人中心：主要是对当前登录用户的个人信息进行便捷修改的功能。

广告管理：主要对各终端的广告数据进行管理维护。

站点栏目：主要对大型系统网站等栏目进行划分和维护的模块。

会员管理：对各终端注册的会员进行统一的查询与管理的模块。

网站配置：对配置管理模块的数据源动态解析与统一维护管理的模块。

通知公告：系统通知公告信息发布维护。

代码生成：一键生成模块CRUD的功能，包括后端和前端Vue等相关代码。

<img width="1920" height="942" alt="图片" src="https://github.com/user-attachments/assets/a5f5c406-fa47-473a-a335-a3f4c4478536" />
<img width="1920" height="942" alt="图片" src="https://github.com/user-attachments/assets/0a64b5c1-c477-47af-b201-5c691cd97d6c" />
<img width="1920" height="942" alt="image" src="https://github.com/user-attachments/assets/9f21e56c-6fae-4ef5-b17c-9022fbe155bf" />
<img width="1920" height="942" alt="image" src="https://github.com/user-attachments/assets/aa64f566-67bb-4740-bb7c-69f2f3823894" />
<img width="1678" height="1080" alt="image" src="https://github.com/user-attachments/assets/aab57acd-2be2-4af6-81be-cb254a8fa0c5" />






### 项目介绍

**TfAdmin** 是一款遵循 **MIT** 协议开源的快速开发框架，基于最新版本 **ThinkPHP8** 的极简后台管理系统，在使用 **TfAdmin** 前请认真阅读[《免责声明》](http://tfadmin.tiefen.net/index/index/mianze)并同意该声明。

我们致力于快速开发的底层框架，让项目开发变得更容易。框架提供完善的基础组件以及对应的 API 支持，基于此框架可以快速开发各种 WEB 应用。任何一个系统都不能完全满足所有的业务场景，TfAdmin 免费提供基础底层的功能，这里包括系统权限管理，系统存储配置，站点信息配置，在线生成代码，以及其他常用功能集成等…… 因此 TfAdmin 也被大家定性为快速低代码开发系统。目前已经有许多公司及个人在使用 TfAdmin，通过数据聚合统计已有 3 万多在线运行的项目。

**TfAdmin v8** 是基于 **ThinkPHP8** 的思维重新构建，减少大量原非必需的组件，自建存储层、服务层及队列任务机制，另外还增加了许多友好指令！当前 **v8** 版本已经通过了数个系统实践与测试，过程中不停调整与优化，目前系统模块及生成代码模块已经趋于稳定，现将系统管理 **`app/admin`** 及菜单管理 **`app/admin/Sys.Base`** 定为 **v8** 内核两大模块并以 **MIT** 协议发布，后续可能还有其他模块及相关辅助模块更新发布，敬请期待……

当前 **TfAdmin** 的最新版本为 **v8.0**，从这个版本开始正式进入低代码时代，仅需在 **菜单管理** 中创建出数据字段即可在线生成代码
并且框架（composer）中集成了 微信接口** (v2、v3) ** 支付宝接口** （vendor/zoujingli/wechat-developer（在此感谢ThinkAdmin创作团队开源的插件包））可快速融合微信服务开发。
以及 **令牌验证** 服务（vendor/lcobucci (在此感谢jwt创作团队)）api接口服务可在中间件中直接调用令牌验证。**TfAdmin** 与传统
**ThinkPHP** 多应用模式无差别，用户可以自行开发自己的模块，此次升级可完美兼容 **ThinkAdmin v8.0**
应用。我们强烈建议不要占用或修改 **`app/admin/Sys`** 目录里面的代码，这些未来可以关注本站
进行功能及安全升级。


二次开发使用 **TfAdmin** 需要掌握 **ThinkPHP**、**Vue**、**ElementUi** 等开发技能，后台 **UI** 界面基于最新版本的 **ElementUi** 前端框架以及组件加载方式，默认加载了所有 **ElementUi** 的组件，框架中可以直接使用组件（独立页面需要注意 **js** 加载顺序哦），自定义模块代码可以通过在线生成的方式直接生成代码，特殊需求可以手动修改生成后的代码。

### 注意事项

* **TfAdmin** 是基于国内最流行的 **ThinkPHP8** 框架开发，要求在不低于 **PHP 8.0.0** 的版本上运行，如果使用低版本的 **PHP** 可能会影响 **Composer** 依赖组件的安装，或将存在一定的安全隐患；
* 运行环境仅需满足ThinkPHP8的框架环境即可运行；
* 代码仓库下载的文件没有appid和secrect，需要用户自行前往本司[官方平台](http://tfadmin.tiefen.net/customer/login/index)注册后获取；
* 为保持系统可持续在线升级，建议不要在 **app/admin/sys** 目录创建或修改文件，可以自行创建其他模块再编写自己的业务代码。
* 系统是基于严格类型 **PHP** 新特性开发，务必使用专业的 **IDE** ( 如：**PhpStorm**、**NetBeans**、**VsCode**、**Eclipse for PHP** 等 ) 进行项目开发以达到更好的体验与更高的效率！


## 系统部署

通过 git 下载源码, 前往本司
[官方平台]
(http://tfadmin.tiefen.net/customer/login/index)
注册账号，创建应用已获取生成代码appid和secrect。


> **A. 测试或体验环境**
>
系统默认使用`MySQL`数据库，部署方式与ThinkPHP8并无不同，进入后台登录界面后，使用系统默认的账号`admin`和密码`admin888`登录管理后台。

> **B. 开发或线上环境**
>
> 通过数据库管理工具创建空数据库，并将数据库参数配置到`config/database.php`；
> 线上环境还需要安装`Nginx`或`Apache`等`Web`服务 (
> 推荐使用[宝塔](https://www.bt.cn/)集成环境 )，并按照`ThinkPHP8`系统要求配置网站参数。


## 注解生成代码

注解生成代码是指通过方法注释来实现后台模块代码生成管理，用注解来控制功能生成（生成的代码默认开启）。

开发人员只需要写好注释，代码的方法会自动生成。

* 此版本的权限使用注解实现生成代码
* 注释必须标准的块注释，如下案例
* 其中`@buildcode true`表示可以生成代码

```PHP
/**
* 操作的名称
* @buildcode true  # 表示可以生成代码
* /
公共函数 index(){
   // @待办事项
}
```

## 代码仓库

主仓库放置于`Gitee`, `Github`为镜像仓库。

部分代码来自互联网，若有异议可以联系作者进行删除。

* 在线体验地址：http://demo.tiefen.net/ （账号: yanshi 密码: yanshi123）
* Gitee仓库地址：https://gitee.com/yaofu888/tfadmin.git
* GitHub仓库地址：https://github.com/yangshuang-afk/tfadmin.git

## 技术支持

开发前请认真阅读 ThinkPHP 官方文档，会对您有帮助哦！

官方地址及开发指南：https://kdocs.cn/l/clrhzDKOJXWi ，如果实在无法解决问题，可以加官方qq免费交流。

**1.官方QQ：** 7777719

**2.官方微信**

<img src="https://camo.githubusercontent.com/819a42a00ffc49b5f7795bf86460c99cc959e0334b806f02b57e7aece41b341b/687474703a2f2f746661646d696e2e74696566656e2e6e65742f77782e706e67"  width="250">


## 版权信息

[**TfAdmin**](http://tfadmin.tiefen.net/) 遵循 [**MIT**](LICENSE) 开源协议发布，并免费提供使用。

使用本代码需遵守 [LICENSE](LICENSE) 及[《免责声明》](http://tfadmin.tiefen.net/index/index/mianze)。作者不对任何直接/间接后果负责，请自行评估风险。

本项目包含的第三方源码和二进制文件的版权信息另行标注。

版权所有 Copyright © 2005-2025 by TfAdmin (http://tfadmin.tiefen.net/) All rights reserved。

更多细节参阅 [`LISENSE`](LICENSE) 文件

