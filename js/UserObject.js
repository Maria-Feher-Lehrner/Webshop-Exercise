class UserObject {
    constructor() {
        this.$loginButton = $('#login')
        this.$sendLoginButton = $('#submit-login')
        this.$logoutButton = $('#logout')
        this.$loginUserName = $('#username')
        this.$loginPassword = $('#password')
        this.$orderHistoryNavigation = $('#order-history')

        this.token = ""

    }

    initGui() {
        this.checkLoginState();

        this.$sendLoginButton.on('click', (event) => this.startLoginProcess(event))
        this.$logoutButton.on('click', () => this.sendLogoutRequest())
        $(document).on('click', '#order-history', (event) => {
            this.sendOrderHistoryRequest()
        })
        $(document).on('click', '#order-button', () => this.sendOrderRequest())
    }

    checkLoginState() {
        const loginState = localStorage.getItem('loginState')
        const token = localStorage.getItem('token')

        if (loginState === 'loggedIn' && token) {
            this.token = token
            this.$logoutButton.removeClass('disabled')
            this.$orderHistoryNavigation.removeClass('disabled')
            this.$loginButton.addClass('disabled')
        } else {
            this.$logoutButton.addClass('disabled')
            this.$orderHistoryNavigation.addClass('disabled')
            this.$loginButton.removeClass('disabled')
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
        if (this.token != null){
            $('#loginModal').modal('hide')

            localStorage.setItem('token', this.token)
            localStorage.setItem('loginState', 'loggedIn')
            this.checkLoginState()
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
        if (response.state === 'OK'){
            localStorage.removeItem('token')
            localStorage.setItem('loginState', 'loggedOut')
            this.checkLoginState()
        }
    }

    sendOrderRequest(){
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
            })
    }

    displayOrderFeedback() {
        let $orderSuccessModal = $('#orderModal')
        $orderSuccessModal.modal('show')
    }

    sendOrderHistoryRequest(){
        //event.preventDefault();
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
            .fail(function (error) {
                console.log(error)
            })
    }

    renderOrderHistory(response) {
        if(response.error){
            this.showErrorModal()
        }
        this.addOrderHistoryItems(response.orders)
    }
    showErrorModal() {
        let $errorModal = $('#errorModal')
        $errorModal.modal('show')
    }
    addOrderHistoryItems(ordersArray) {
        let $firstAccordionItem = $('#accordion-item-1')
        this.updateFirstOrderHistoryItem(ordersArray, $firstAccordionItem)
        this.addRemainingOrderHistoryItems(ordersArray, $firstAccordionItem)
    }

    updateFirstOrderHistoryItem(ordersArray, $firstAccordionItem) {
        let date = ordersArray[0].date
        let totalPrice = ordersArray[0].total

        $firstAccordionItem.find('button').text('Bestelldatum: ' + date)
        $firstAccordionItem.find('.accordion-body').text('Gesamtpreis : ' + totalPrice)
    }

    addRemainingOrderHistoryItems(ordersArray, $firstAccordionItem) {
        for(let i= 1; i < ordersArray.length; i++){
            let nextDate = ordersArray[i].date
            let nextTotalPrice = ordersArray[i].total
            console.log(nextTotalPrice)

            let $nextAccordionItem = $firstAccordionItem.clone()
            $nextAccordionItem.attr("id", "accordion-item-" + (i+1))
            $nextAccordionItem.find('.accordion-button')
                .attr('data-bs-target', '#collapse' + (i + 1))
                .attr('aria-controls', 'collapse' + (i + 1))
                .text('Bestelldatum: ' + nextDate);
            $nextAccordionItem.find('.accordion-collapse')
                .attr('id', 'collapse' + (i + 1))
                .attr('aria-labelledby', 'heading' + (i + 1))
                .removeClass('show');
            $nextAccordionItem.find('.accordion-body').text('Gesamtpreis : ' + nextTotalPrice);

            $('#order-history-container').append($nextAccordionItem);
        }
    }
}