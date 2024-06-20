class UserObject {
    constructor() {
        this.$loginButton = $('#login')
        this.$sendLoginButton = $('#submit-login')
        this.$logoutButton = $('#logout')
        this.$loginUserName = $('#username')
        this.$loginPassword = $('#password')

        this.$orderButton = $('#order-button')
        //this.$orderSuccessInfo = $('#orderModal')
        this.$orderHistoryNavigation = $('#order-history')

        this.token = ""

    }

    initGui() {
        this.checkLoginState();

        this.$sendLoginButton.on('click', (event) => this.handleLogin(event));
        this.$logoutButton.on('click', () => this.sendLogoutRequest());

        this.$orderButton.on('click', () => this.sendOrderRequest())
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
        //console.log(`Username: ${username}`);
        //console.log(`Password: ${password}`);

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
        //console.log(response)
        this.token = response.token
        //console.log(this.token)
        if (this.token != null){
            $('#loginModal').modal('hide');
            /*this.$logoutButton.removeClass('disabled')
            this.$orderHistoryNavigation.removeClass('disabled')
            this.$loginButton.addClass('disabled')*/

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
        //console.log(response)
        if (response.state === 'OK'){
            /*this.$logoutButton.addClass('disabled')
            this.$orderHistoryNavigation.addClass('disabled')
            this.$loginButton.removeClass('disabled')*/

            localStorage.removeItem('token');
            localStorage.setItem('loginState', 'loggedOut');
            this.checkLoginState()
        }
    }

    sendOrderRequest(){
        $.ajax({
            url: "api/index.php?resource=orders",
            method: 'POST'
        })
            .done((response) => {
                this.displayOrderFeedback(response)
            })
            .fail(function (error) {
                console.log(error)
            })
    }

    displayOrderFeedback(response) {
        let $orderSuccessModal = $('#orderModal')
        $orderSuccessModal.modal('show');
    }
}