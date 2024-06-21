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

        this.$sendLoginButton.on('click', (event) => this.handleLogin(event));
        this.$logoutButton.on('click', () => this.sendLogoutRequest());
        this.$orderHistoryNavigation.on('click', () => this.sendOrderHistoryRequest())
        $(document).on('click', '#order-button', () => this.sendOrderRequest());
    }

    checkLoginState() {
        const loginState = localStorage.getItem('loginState');
        const token = localStorage.getItem('token');

        if (loginState === 'loggedIn' && token) {
            this.token = token;
            this.$logoutButton.removeClass('disabled');
            this.$orderHistoryNavigation.removeClass('disabled');
            this.$loginButton.addClass('disabled');
        } else {
            this.$logoutButton.addClass('disabled');
            this.$orderHistoryNavigation.addClass('disabled');
            this.$loginButton.removeClass('disabled');
        }
    }

    handleLogin(event) {
        event.preventDefault();
        let username = this.$loginUserName.val();
        let password = this.$loginPassword.val();

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
                this.triggerLoginGuiChanges(response)
            })
            .fail(function (error) {
                console.log(error)
            })
    }
    triggerLoginGuiChanges(response) {
        this.token = response.token
        if (this.token != null){
            $('#loginModal').modal('hide');

            localStorage.setItem('token', this.token);
            localStorage.setItem('loginState', 'loggedIn');
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
            localStorage.removeItem('token');
            localStorage.setItem('loginState', 'loggedOut');
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
        $orderSuccessModal.modal('show');
    }

    sendOrderHistoryRequest(){

    }
}