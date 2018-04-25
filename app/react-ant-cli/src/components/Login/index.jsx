import React, { Component } from 'react'
import './index.less'

class Login extends Component{

	stopPropagation=(e)=>{
		e.stopPropagation()
	}

	render(){
		return(
			<div className='login-box' onClick={this.props.handle}>
				<div className='content' onClick={this.stopPropagation}>
					登录
				</div>
			</div>
		)
	}
}

export default Login