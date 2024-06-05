class CartObject {
    constructor() {
        this.$navToCart = $('#cart-nav')
        this.$webshop = $('#webshop')
        this.$cart = $('#warenkorb')
    }

    initGui() {
        this.$navToCart.on('click', () => this.buildWebshopView())
        //event delegation for dynamically created buttons
        $(document).on('click', '.add', (event) => this.addProductToCart(event))
        $(document).on('click', '.subtract', (event) => this.subtractProductFromCart(event))
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
                this.drawAndFillCartSpace(response)
                console.log(response)
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    drawAndFillCartSpace(response) {
        this.$webshop.removeClass('col-12').addClass('col-lg-6 col-md-12')
        this.$cart.removeClass('col-0').addClass('col-lg-6 col-md-12')
        this.$cart.empty()
        this.addHeadline()
        this.drawAndFillTable(response)
    }
    addHeadline() {
        let headline = $("<h1>").text("Warenkorb")
        this.$cart.append(headline)
    }
    drawAndFillTable(response) {
        let table = $("<table>").addClass("table")
        this.drawHeader(table)
        this.appendTableRows(response.cart, table)
        this.appendSummaryRow(response, table)

        this.$cart.append(table)
    }
    drawHeader(table) {
        let tableHead = $("<thead>")
        let headerRow = $("<tr>")
        let headerProduct = $("<th>").text("Artikel").attr("scope", "col")
        let headerAmount = $("<th>").text("Stk.").attr("scope", "col")
        let headerPrice = $("<th>").text("Preis").attr("scope", "col")

        headerRow.append(headerProduct, headerAmount, headerPrice);
        tableHead.append(headerRow)
        table.append(tableHead)
    }
    appendTableRows(array, table) {
        let tableBody = $("<tbody>")

        for (let item of array) {
            let tr = $("<tr>")

            let tdArticleName = $("<td>").text(item.articleName)
            let tdAmount = $("<td>")
            this.drawElementAmount(tdAmount, item)
            let tdPrice = $("<td>").text("€ " + item.itemTotal)

            tr.append(tdArticleName, tdAmount, tdPrice)
            tableBody.append(tr)
        }
        table.append(tableBody)
    }
    drawElementAmount(tdElement, item){

        let buttonPlus = $("<button>").addClass("btn btn-outline-dark m-1 add").attr("type", "button").data("article-id", item.articleId)
        let buttonMinus = $("<button>").addClass("btn btn-outline-dark m-1 px-2.5 subtract").attr("type", "button").data("article-id", item.articleId)
        buttonPlus.text("+")
        buttonMinus.text("-")
        tdElement.text(item.amount)
        tdElement.append(buttonPlus, buttonMinus)
    }
    appendSummaryRow(response, table){
        let trSummary = $("<tr>").addClass("summaryRow")
        let thSummary = $("<th>").text("Gesamtsumme").attr("colspan", "2")
        let tdTotal = $("<td>").text("€ " + response.totalPrice).addClass("fw-bold")

        trSummary.append(thSummary, tdTotal)
        table.append(trSummary)
    }



    addProductToCart(event) {
        let $articleId = $(event.currentTarget).data('article-id')
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
        let $articleId = $(event.currentTarget).data('article-id')
        let url = "api/index.php?resource=cart&articleId=" + $articleId
       // let url = "api/index.php?resource=cart"

        $.ajax({
            url: url,
            method: 'DELETE'
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