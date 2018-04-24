let baseURL,
	ENV = process.env.NODE_ENV


if(ENV==="development"){
	// 开发环境中使用代理避免跨域问题，留空
	baseURL=''
}else{
	// 生产环境中接口地址
	baseURL='/index.php/'
}

export {
	baseURL,
}