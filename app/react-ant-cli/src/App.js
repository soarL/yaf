import React, { Component } from 'react';
import { Route , Switch , HashRouter } from 'react-router-dom'
// import Loadable from 'react-loadable'
import './App.less'

// 只负责显示的组件
// import Loading from '@/components/Loading'
import Header from '@/components/Header'

// 容器组件 业务组件就是有状态的组件//按需加载
import Home from '@/containers/Home'

// const AsyncAntdBase = Loadable({
//   loader: () => import('@/components/Antd/base'),
//   loading: loading
// })

class App extends Component {
  // componentDidMount() {
    // 做于预渲染
    // AsyncAntdBase.preload()

  // }

  render() {
    return (
        <HashRouter>
            <div>
              <Header/>
              <div className='container body-box'>
                <Switch>
                 <Route path="/" exact component={ Home } />
                </Switch>
              </div>
            </div>
       </HashRouter>
    )
  }
}


export default App