import promise from 'es6-promise'
import * as config from '@/config' 
promise.polyfill();
const  axios = require('axios')

/**
 * @params method
 * @params url {string} 请求地址  例如：/login 配合baseURL组成完整请求地址
 * @params baseURL {string} 请求地址统一前缀 ***需要提前指定*** 
 * @params timeout {number} 请求超时时间 默认 30000
 * @params headers {string} 指定请求头信息
 * @params validateStatus {func} 默认判断请求成功的范围 200 - 300
*/

export default class Server {

  GET(url,params={},option={}){
    return new Promise((resolve, reject) => {
      let _options = {
        method:"get",
        url,
        baseURL:config.baseURL,
        params,
        timeout:30000,
        validateStatus:(status)=>{
            return status >= 200 && status < 300;
        },
        ...option
      }
      axios.request(_options).then(res => {
        resolve(typeof res.data === 'object' ? res.data : JSON.parse(res.data))
      },error => {
        if(error.response){
            reject(error.response.data)
        }else{
            reject(error)
        }
      })
    })
  }
  
  POST(url,data={},option={}){
    return new Promise((resolve, reject) => {
      let _options = {
        method:"post",
        url,
        baseURL:config.baseURL,
        data,
        headers: {
          // form-data提交
          'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With':'XMLHttpRequest'
        },
        transformRequest: [function (data) {
             let ret = ''
             for (let it in data) {
               ret += encodeURIComponent(it) + '=' + encodeURIComponent(data[it]) + '&'
             }
             return ret
        }],
        timeout:30000,
        validateStatus:(status)=>{
            return status >= 200 && status < 300;
        },
        ...option
      }

      axios.request(_options).then(res => {
        resolve(typeof res.data === 'object' ? res.data : JSON.parse(res.data))
      },error => {
        if(error.response){
            reject(error.response.data)
        }else{
            reject(error)
        }
      })
    })
  }

  use(){
    console.log('自己看文档啊')    
  }
  
}
