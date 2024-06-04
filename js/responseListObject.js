class responseListObject {
    constructor() {
        this.$categoriesOutput = $('#categories-output')
        this.$productOutput = $('#products-output')

    }

    initGui() {
        this.loadCategoryData()
        this.setupEventHandlers()
    }

    loadCategoryData() {
        $.ajax({
            url: "api/index.php?resource=types",
            method: 'GET'
        })
            .done((response) => {
                //console.log(response[0]["productType"])
                //console.log(response[0]["url"])
                this.updateCategoriesList(response)
            })
            .fail(function (error) {
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
                let productType = response.productType
                let products = response.products
                this.updateProductsList(products)
                this.createHeadline(productType)
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    updateCategoriesList(response) {
        for (let item of response) {
            let listElement = $("<a>").addClass("list-group-item list-group-item-action");
            listElement.text(item.productType)
            //console.log(item.productType)
            listElement.attr("data-url", item.url)
            this.$categoriesOutput.append(listElement)

            //Disables the three categories that are emtpy
            //TODO: in eigene Methode auslagern - Version weiter unten hat unerwuenschten Effekt arbeitet nicht wie erwartet
            //TODO: Methode evtl. dynamisch bauen, damit man nicht vorher schon wissen muss welche Kategorien leer sind?
            //this.disableEmptyCategories(item)
            if (item.productType === "mouth care" ||
                item.productType === "shave" ||
                item.productType === "wellness") {
                listElement.addClass("disabled text-body-tertiary")
                listElement.attr("disabled", "true")
            }
        }
    }

    disableEmptyCategories(item) {
        if (item.productType === "mouth care" ||
            item.productType === "shave" ||
            item.productType === "wellness") {
            listElement.addClass("disabled")
            listElement.attr("disabled", "true")
        }
    }

    createHeadline(productType) {
        let headline = $("<h2>")
        headline.text(productType.toUpperCase())
        this.$productOutput.prepend(headline)
    }

    updateProductsList(products) {
        this.$productOutput.empty()

        //Platzhalter-Id für Bilder
        let id = 1
        //TODO: Backend umbauen und richtige Produkt-IDs mit Daten mitgeben
        for (let product of products) {
            let colDiv = this.generateCardColumn()
            let cardDiv = this.generateCard()
            let cardImg = this.generateCardImg(id)
            let cardBody = this.generateCardBody(product)

            cardDiv.append(cardImg)
            cardDiv.append(cardBody)
            colDiv.append(cardDiv)
            this.$productOutput.append(colDiv)

            id++
        }
    }

    generateCardColumn() {
        return $("<div>").addClass("col-sm-6 col-md-4 col-lg-3 ml-3 mr-3")
    }

    generateCard(){
        return $("<div>").addClass("card mb-3").attr("style", "width: 18rem;")
    }
    generateCardImg(id) {
        let cardImg = $("<img>").addClass("card-img-top")
        cardImg.attr("src", "assets/" + id + ".png")
        cardImg.attr("alt", "")
        return cardImg
    }

    generateCardBody(product) {
        let cardBody = $("<div>").addClass("card-body")

        let p = $("<p>").addClass("card-title")
        p.text(product.name)
        cardBody.append(p)
        return cardBody
    }

    setupEventHandlers() {
        this.$categoriesOutput.on('click', 'a', (event) => {
            event.preventDefault();
            let url = $(event.currentTarget).attr('data-url');
            this.loadProductsData(url);
        });
    }
}


//Alternative Umsetzung typeID aus URL parsen. Z. B.:
//let url = "http://....filter-type=4"
//url.split("=").pop()
//url.split() gibt ein Array aus. Das heisst hier kommt wieder ein key value pair raus.
//.pop() greift auf das letzte Element zu
//("=")[4] greift auf das 4.= ebenfalls das letzte Element zu


//Beispiel: Falls 1. Listelement die Ueberschrift ist, muss empty() function alle Elemente bis auf das erste loeschen
//$list.find('.list-group-element').not(':first').remove()
