class UserObject {
    constructor() {
        this.$loginButton = $('#login')
        this.$sendLoginButton = $('#submit-login')
        this.$logoutButton = $('#logout')
        this.$loginUserName = $('#username')
        this.$loginPassword = $('#password')
        this.$loginError = $('#login-error')
        this.$orderHistoryNavigation = $('#order-history')

        this.token = ""
        this.checkLoginState();
        this.initGui()

    }

    initGui() {
        this.checkLoginState();

        this.$sendLoginButton.on('click', (event) => this.startLoginProcess(event))
        this.$logoutButton.on('click', () => this.sendLogoutRequest())
        $(document).on('click', '#order-history', (event) => this.handleOrderHistoryNavigation(event))
        $(document).on('click', '#order-button', () => this.sendOrderRequest())
    }

    checkLoginState() {
        const loginState = localStorage.getItem('loginState')
        const token = localStorage.getItem('token')

        if (loginState === 'loggedIn' && token) {
            this.token = token
            console.log("Token retrieved from localStorage:", this.token)
            this.$logoutButton.removeClass('disabled')
            this.$orderHistoryNavigation.removeClass('disabled')
            this.$loginButton.addClass('disabled')
        } else {
            this.$logoutButton.addClass('disabled')
            this.$orderHistoryNavigation.addClass('disabled')
            this.$loginButton.removeClass('disabled')
        }

        this.checkCurrentPage()
    }
    checkCurrentPage() {
        if (window.location.pathname.includes('orders.html')) {
            //this.loadOrderHistory()
            this.sendOrderHistoryRequest()
        }
    }

    startLoginProcess(event) {
        event.preventDefault();
        let username = this.$loginUserName.val()
        let password = this.$loginPassword.val()

        this.sendLoginRequest(username, password)
    }

    sendLoginRequest(username, password) {
        $.ajax({
            url: "api/index.php?action=login",
            method: 'POST',
            data: {
                username: username,
                password: password
            }
        })
            .done((response) => {
                this.triggerLoginChanges(response)
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    triggerLoginChanges(response) {
        this.token = response.token
        if (this.token != null) {
            $('#loginModal').modal('hide')

            localStorage.setItem('token', this.token)
            localStorage.setItem('loginState', 'loggedIn')
            this.checkLoginState()
        }
        else {
            this.$loginError.text('Login gescheitert: Username oder Passwort falsch!')
        }
    }

    sendLogoutRequest() {
        $.ajax({
            url: "api/index.php?action=logout",
            method: 'POST'
        })
            .done((response) => {
                this.triggerLogoutGuiChanges(response)
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    triggerLogoutGuiChanges(response) {
        if (response.state === 'OK') {
            localStorage.removeItem('token')
            localStorage.setItem('loginState', 'loggedOut')
            this.checkLoginState()
        }
    }

    sendOrderRequest() {
        $.ajax({
            url: "api/index.php?resource=orders",
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + this.token
            }
        })
            .done((response) => {
                this.displayOrderFeedback(response)
            })
            .fail(function (error) {
                console.log(error)
                //TODO:implement feedmack message for invalid order request (e.g. if user is not logged in)
            })
    }

    displayOrderFeedback() {
        let $orderSuccessModal = $('#order-Modal')
        $orderSuccessModal.modal('show')
    }

    handleOrderHistoryNavigation(event) {
        event.preventDefault()
        console.log("Meine Bestellungen was clicked")
        window.location.href = 'orders.html'
    }

    sendOrderHistoryRequest() {
        console.log("Sending order history request with token:", this.token)
        $.ajax({
            url: "api/index.php?resource=orders",
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + this.token
            }
        })
            .done((response) => {
                console.log(response)
                this.renderOrderHistory(response)
            })
            .fail((error) => {
                console.log(error)
                this.showErrorModal()
            })
    }
    renderOrderHistory(response) {
        $('#order-history-container').empty();

        if (response.error) {
            this.showErrorModal()
        }
        this.addOrderHistoryItems(response.orders)
    }
    showErrorModal() {
        let $errorModal = $('#errorModal')
        $errorModal.modal('show')
    }
    addOrderHistoryItems(ordersArray) {
        for (let i = 0; i < ordersArray.length; i++) {
            let $accordionItem = $('<div class="accordion-item" id="accordion-item-' + (i + 1) + '"></div>');
            let $accordionHeader = $('<h2 class="accordion-header" id="heading-' + (i + 1) + '"></h2>');
            let $accordionButton = $('<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' + (i + 1) + '" aria-expanded="true" aria-controls="collapse-' + (i + 1) + '"></button>');
            let $accordionCollapse = $('<div id="collapse-' + (i + 1) + '" class="accordion-collapse collapse" aria-labelledby="heading-' + (i + 1) + '" data-bs-parent="#order-history-container"></div>');
            let $accordionBody = $('<div class="accordion-body"></div>');

            let date = ordersArray[i].date;
            let totalPrice = ordersArray[i].total;

            $accordionButton.text('Bestelldatum: ' + date);
            $accordionBody.text('Gesamtpreis: ' + totalPrice);

            $accordionHeader.append($accordionButton);
            $accordionCollapse.append($accordionBody);
            $accordionItem.append($accordionHeader);
            $accordionItem.append($accordionCollapse);

            $('#order-history-container').append($accordionItem);
        }
    }
}