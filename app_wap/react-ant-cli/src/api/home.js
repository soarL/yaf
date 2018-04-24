import Server from '@/api/server'

class Home extends Server{
	async getData(){
		let data = await this.GET('/index')
		return data
	}
}

export default new Home()