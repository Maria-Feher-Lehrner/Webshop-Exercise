$( document ).ready(function(){
    console.log("Document is ready")
    let listObject = new productListObject()
    listObject.initGui()

    let cartObject = new CartObject()
    cartObject.initGui()

    let userObject = new UserObject()
    userObject.initGui()
});