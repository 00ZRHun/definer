export default {
    host: 'http://simplerevise.com',

    // 获取文件拓展名
    getFileLastName (name){
        name = name.split('.');
        name = name[name.length - 1];
        return name.toLowerCase();
    }
}