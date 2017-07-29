/**
 * Created by Евгения on 26.05.2017.
 */
var site = {
    data: {},
    addMarkToCategory: function (callback) {
        $.ajax({
            'url': '/mark/add',
            'type': 'POST',
            'data': site.data,
            'success': callback
        })
    },
    markDelete: function (callback) {
        $.ajax({
            'url': '/mark/delete',
            'type': 'POST',
            'data': site.data,
            'success': callback
        })
    },
    addModelToMark: function (callback) {
        $.ajax({
            'url': '/model/add',
            'type': 'POST',
            'data': site.data,
            'success': callback
        })
    },
    modelDelete: function (callback) {
        $.ajax({
            'url': '/model/delete',
            'type': 'POST',
            'data': site.data,
            'success': callback
        })
    },
    addPerson:function (callback) {
        $.ajax({
            'url':'/person/add',
            'type':'POST',
            'data':site.data,
            'success':callback
        })
    },
    deletePerson:function (callback) {
        $.ajax({
            'url':'/person/delete',
            'type':'POST',
            'data':site.data,
            'success':callback
        })
    },
    getStatus:function (callback) {
        $.ajax({
            'url':'/get/status',
            'type':'POST',
            'success':callback
        })
    },
    changeStatus:function (callback) {
        $.ajax({
            'url':'/change/status',
            'type':'POST',
            'data':site.data,
            'success':callback
        })
    },
    deletePc:function (callback) {
        $.ajax({
            'url':'/pc/delete',
            'type':'POST',
            'data':site.data,
            'success':callback
        })
    }
}