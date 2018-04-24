import React,{ Component } from 'react'
import { Spin ,Alert} from 'antd'
import './index.less'

class Loading extends Component{

	render(){
		if(this.props.error){
			return(
				<Alert
			     message="渲染出错,请刷新该页面!"
			     description="可能由于网络延迟导致,如果刷新无法恢复正常请ctrl+f5 强制刷新页面!或者试图联系网站客服人员!"
			     type="error"
				/>
			)
		}else{
			return (
				<div className="loading">
					<Spin size="large" />	
				</div>
			)
		}
	}
}

export default Loading