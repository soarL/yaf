#time 2017 3 18 
#作者 lzt

#结构编写
```
├─build	 ---编译后存放的文件夹
├─config  ---webpack 配置信息
├─node_modules  ---node 模块包
├─public  --- 不会被webpack编译
├─scripts	---npm指令入口
└─src ---项目文件夹
```

#相关技术栈
1、React  + React-Router-dom  
2、Redux   react-redux 状态管理插件
3、highcharts 画表格插件
4、ant design UI组件库

命名规范

css篇
    构建工具配置less，管理组件css，最外层css以组件名字命名、以避免css样式冲突。
    全部小写 使用 “-”链接 示例    form-title 
    App.less 为全局共有css样式
    theme.js 为定制ant design 定制主题色的文件 

js篇

   全局配置
config

    函数命名
小驼峰方式命名。serverPost

   类命名
大驼峰方式。例如 ServerPost

   REACT组件
分成两种组件，UI组件和容器组件，严格区分两种组件
UI组件：只负责试图展示无业务逻辑代码不依赖别他组件 -----对应文件夹 components

```
components
-----Header
----------asset
---------------xxx.png
----------index.jsx
----------index.less
-----index.js
```
_______________________________________________________
-------文件夹规范
出口文件 components/index.js //所有组件注册完成后都需在这里导出
文件夹以大写开头按功能命名。基础文件  index.jsx
组件的样式 index.less
组件自己的静态资源 asset 文件夹

    容器组件：负责处理业务逻辑组件  -----对应文件夹 containers

```
containers
-----Home
----------index.jsx
----------index.less
----------asset
---------------xxx.png
```
_______________________________________________________

-------文件夹规范
文件夹以大写开头按功能命名。基础文件  index.jsx
组件的样式 index.less
组件自己的静态资源 asset 文件夹

 
    数据层
所有的从服务器获取的数据全部在 api 文件夹下处理。然后在容器组件中去调用方法获取数据
以面向对象形式构造，继承 Server类，所有的方法必须写成  async 函数  
大概可以理解成这个文件夹就是 后端 MVC框架中的 M module层 负责获取数据 整理数据 过滤数据 等操作 所以一定要以class 来写 方便使用,和维护在别他页面只要调用封装好的方法获取数据即可