import React,{ Component } from 'react'
import {
	Input
} from 'antd'
import './index.less'

const SearchInput = Input.Search

class Search extends Component{

	stopPropagation=(e)=>{
		e.stopPropagation();
	}

	searchBegin = (e)=>{
		console.log(e)
	}

	render(){
		return(
			<div className='search-box' onClick={this.props.handle}>
				<div onClick={this.stopPropagation} className='content'>
					<SearchInput
				      placeholder="请输入您想要的内容"
				      onSearch={this.searchBegin}
				      enterButton
				    />
				</div>
			</div>
		)
	}
}
export default Search