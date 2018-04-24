import React,{ Component } from 'react'
import {Link} from 'react-router-dom'
import Menu from './menu'
import {
	Row,
	Col,
	Button,
} from 'antd'

import './index.less'




class Header extends Component{
	constructor(props){
		super(props)
		this.state = {
			collapsed:false
		}
	}
	menuChange = ()=>{
		this.setState({
			collapsed:!this.state.collapsed 
		})
	}
	render(){
		return(
			<div className='header-box'>
				<div className='container'>
					<Row className='title'>
						<Col span={16} className='logo'>
							<Link to='/'><img src="http://react-china.org/uploads/default/38/c4b96a594bd352e0.png" alt="logo"/></Link>
						</Col>
						<Col span={8} className='menu'>
							<Button type="primary" size='small'>注册</Button>
							<Button icon='user' type="primary" size='small'>登录</Button>
							<Button shape="circle" icon="search" size='small'/>
							<Button shape="circle" icon={ this.state.collapsed ? 'menu-unfold' : 'menu-fold'} size='small' onClick={this.menuChange}/>
							{this.state.collapsed ? <Menu/> : ''}
						</Col>
					</Row>
				</div>
			</div>
		)
	}
}

export default Header