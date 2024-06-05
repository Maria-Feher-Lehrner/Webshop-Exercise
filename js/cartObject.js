class CartObject {
    constructor() {
        this.$navToCart = $('#cart-nav')
        this.$webshop = $('#webshop')
        this.$cart = $('#warenkorb')
    }

    initGui() {
        this.$navToCart.on('click', () => this.buildWebshopView())
        //event delegation for dynamically created buttons
        $(document).on('click', '.addToCart', (event) => this.addProductToCart(event))
    }

    buildWebshopView() {
        this.loadCartData()

    }

    loadCartData() {
        $.ajax({
            url: "api/index.php?resource=cart",
            method: "GET"
        })
            .done((response) => {
                this.makeAndFillCartSpace(response)
                console.log(response)
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    makeAndFillCartSpace($response) {
        this.$webshop.removeClass('col-12').addClass('col-lg-6 col-md-12')
        this.$cart.removeClass('col-0').addClass('col-lg-6 col-md-12')
        this.$cart.empty()
        this.addHeadline()
        this.makeAndFillTable($response.cart)
    }

    addHeadline() {
        let headline = $("<h1>").text("Warenkorb")
        this.$cart.append(headline)
    }

    makeAndFillTable(cartArray) {
        let table = $("<table>").addClass("table")
        this.drawHeader(table)
        this.appendTableRows(cartArray, table)
        this.appendSummaryRow(table)

        this.$cart.append(table)
    }

    drawHeader(table) {
        let headerRow = $("<tr>")
        let headerProduct = $("<th>").text("Artikel").attr("scope", "col")
        let headerAmount = $("<th>").text("Stk.").attr("scope", "col")
        let headerPrice = $("<th>").text("Preis").attr("scope", "col")

        headerRow.append(headerProduct, headerAmount, headerPrice);
        table.append(headerRow)
    }

    appendTableRows(array, table) {
        for (let item of array) {
            let tr = $("<tr>")

            let tdArticleName = $("<td>").text(item.articleName)
            let tdAmount = $("<td>").text(item.amount)
            let tdPrice = $("<td>")

            tr.append(tdArticleName, tdAmount, tdPrice)
            table.append(tr)
        }
    }
    //TODO: in appendSummaryRow noch response übergeben und Gesamtpreis ausgeben
    appendSummaryRow(table){
        let trSummary = $("<tr>")
        let thSummary = $("<th>").text("Gesamtsumme").attr("colspan", "2")
        let tdTotal = $("<td>").text("Summe")
        //TODO Gesamtpreis dynamisch holen

        trSummary.append(thSummary, tdTotal)
        table.append(trSummary)
    }


    addProductToCart(event) {
        let $articleId = $(event.currentTarget).attr('id')
        let url = "api/index.php?resource=cart&articleId=" + $articleId

        $.ajax({
            url: url,
            method: 'POST'
        })
            .done((response) => {
                this.showSuccessMessage()
                this.loadCartData()
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    subtractProductFromCart(event) {
        let articleId = $(event.currentTarget).attr('class')
        //let url = "api/index.php?resource=cart&articleId=" + $articleId
        let url = "api/index.php?resource=cart"

        $.ajax({
            url: url,
            method: 'DELETE',
            data: {articleId: articleId}
        })
            .done((response) => {
                this.showSuccessMessage()
                this.loadCartData()
            })
            .fail(function (error) {
                console.log(error)
            })
    }


    showSuccessMessage() {
        //TODO: Info Aktion erfolgreich einfügen via Modal
    }


}