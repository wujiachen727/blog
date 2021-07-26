/** EasyWeb iframe v3.1.8 date:2020-05-04 License By http://easyweb.vip */
layui.config({  // common.js是配置layui扩展模块的目录，每个页面都需要引入
    version: '318',   // 更新组件缓存，设为true不缓存，也可以设一个固定值
    base: '/static/admin/assets/module/'
}).extend({
    steps: 'steps/steps',
    notice: 'notice/notice',
    cascader: 'cascader/cascader',
    fileChoose: 'fileChoose/fileChoose',
    Split: 'Split/Split',
    Cropper: 'Cropper/Cropper',
    tagsInput: 'tagsInput/tagsInput',
    citypicker: 'city-picker/city-picker',
    introJs: 'introJs/introJs',
    zTree: 'zTree/zTree'
}).use(['admin'], function () {
    var admin = layui.admin;
});