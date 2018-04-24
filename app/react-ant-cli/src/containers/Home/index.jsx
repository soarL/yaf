import React,{ Component } from 'react'
import homeAPI from '@/api/home'
import './index.less'

class Home extends Component{
	async componentWillMount() {
		let data = await homeAPI.getData()
		console.log(data);
	}
	render(){
		return(
			<div>
				react home 
			</div>
		)
	}
}

export default Home