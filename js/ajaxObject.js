class ajaxObject {
    constructor() {
        this.$action = $()
        this.$categoriesOutput = $('#categories-output')
        this.$productOutput = $('#products-output')

    }

    initGui() {
        this.loadCategoryData()
        this.setupEventHandlers()
    }

    loadCategoryData() {
        $.ajax({
            url: "http://localhost/bb/Webshop/api/index.php?resource=types",
            method: 'GET'
        })
            .done((response) => {
                console.log(response[0]["productType"])
                console.log(response[0]["url"])
                this.updateCategoriesList(response)
            })
            .fail(function(error) {
                console.log(error)
            })
    }

    loadProductsData(url) {
        $.ajax({
            url: url,
            method: 'GET'
        })
            .done((response) => {
                console.log(response)
                this.updateProductsList(response)
            })
            .fail(function(error) {
                console.log(error)
            })
    }

    updateCategoriesList(response) {
        let id = 1
        for (let item of response){
            let listElement = $("<a>").addClass("list-group-item list-group-item-action");
            listElement.text(item.productType)
            console.log(item.productType)
            listElement.attr("href", item.url)
            listElement.attr("id", id)
            this.$categoriesOutput.append(listElement)
            id++

            //Disables the three categories that are emtpy
            //TODO: in eigene Methode auslagern - Version weiter unten hat unerwuenschten Effekt arbeitet nicht wie erwartet
            //TODO: Methode evtl. dynamisch bauen, damit man nicht vorher schon wissen muss welche Kategorien leer sind?
            if(item.productType == "mouth care" ||
                item.productType == "shave" ||
                item.productType == "wellness"){
                listElement.addClass("disabled")
                listElement.attr("disabled", "true")
            }
            //this.disableEmptyCategories(item)
        }
    }

    updateProductsList(response) {

    }
    disableEmptyCategories(item) {
        if(item.productType == "mouth care" ||
            item.productType == "shave" ||
            item.productType == "wellness"){
            listElement.addClass("disabled")
            listElement.attr("disabled", "true")
        }
    }

    setupEventHandlers() {
        this.$categoriesOutput.on('click', 'a', (event) => {
            event.preventDefault();
            let url = $(event.currentTarget).attr('data-url');
            this.loadProductsData(url);
        });
    }


}
