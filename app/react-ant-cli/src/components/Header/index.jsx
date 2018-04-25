import React,{ Component } from 'react'
import {Link} from 'react-router-dom'
import Menu from '@/components/Menu'
import Search from '@/components/Search'
import Register from '@/components/Register'
import Login from '@/components/Login'
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
			collapsed:false,
			searchDispaly:false,
			registerDisplay:false,
			loginDisplay:false
		}
	}

	menuChange = ()=>{
		this.setState({
			collapsed:!this.state.collapsed 
		})
	}

	searchChange = ()=>{
		this.setState({
			searchDispaly:!this.state.searchDispaly
		})
	}
	registerChange=()=>{
		this.setState({
			registerDisplay:!this.state.registerDisplay
		})
	}

	loginChange=()=>{
		this.setState({
			loginDisplay:!this.state.loginDisplay
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
							<Button type="primary" size='small' onClick={this.registerChange}>注册</Button>
							{this.state.registerDisplay ? <Register handle={this.registerChange}/> : ''}
							<Button icon='user' type="primary" size='small' onClick={this.loginChange}>登录</Button>
							{this.state.loginDisplay ? <Login handle={this.loginChange}/> : ''}
							<Button shape="circle" icon="search" size='small' onClick={this.searchChange}/>
							{this.state.searchDispaly ? <Search handle={this.searchChange}/> : ''}
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