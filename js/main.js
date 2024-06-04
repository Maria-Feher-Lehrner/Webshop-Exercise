$( document ).ready(function(){
    console.log("Document is ready")
    let listObject = new responseListObject()
    listObject.initGui()

    let cartObject = new CartObject()
    cartObject.initGui()
});