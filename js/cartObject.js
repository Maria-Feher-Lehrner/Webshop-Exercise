class CartObject {
    constructor() {
        this.$navToCart = $('#cart-nav')
        this.$webshop = $('#webshop')
        this.$cart = $('#warenkorb')


    }

    initGui() {
        this.$navToCart.on('click', (event) => {
            event.preventDefault();
            this.makeCartSpace()
        })
    }

    makeCartSpace() {
        print("This is the makeCartSpace function")
        if (this.$cart.hasClass('col-0')) {
            this.$webshop.removeClass('col-12').addClass('col-6');
            this.$cart.removeClass('col-0').addClass('col-6');
        } else {
            this.$webshop.removeClass('col-6').addClass('col-12');
            this.$cart.removeClass('col-6').addClass('col-0');
        }

        //this.$webshop.removeClass('col-12').addClass('col-lg-6 col-md-12')
        //this.$cart.removeClass('col-0').addClass('col-lg-6 col-md-12')
    }
}