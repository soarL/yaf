import React, { Component } from 'react'
import './index.less'

class Register extends Component{

	stopPropagation=(e)=>{
		e.stopPropagation()
	}

	render(){
		return(
			<div className='register-box' onClick={this.props.handle}>
				<div className='content' onClick={this.stopPropagation}>
					注册
				</div>
			</div>
		)
	}
}

export default Register