app.factory('register',['$http','$window',function($http,$window){
    // Setup
    if ( !$window.hello ){
	$("head").append("<script src='js/hello.min.js' type='text/javascript'></script>");
    }
    // Initialize hello.js
    $window.hello.init({
	"facebook":"164434750353945"
    });
    return {
	register : function(social,info,$scope,callback){
	    if ( !social || social == false ){
		var email = $("#user_email").val();
		var pass  = $("#user_pass").val();
		var conf  = $("#user_pass_confirm").val();
		var univ  = $("[name='university_value']")[0].options[$("[name='university_value']")[0].selectedIndex].innerHTML;
		signUp.reset();
		// Check for empty fields
		if ( email.match(/\S/) == null ){
		    signUp.emailError = true;
		    $("#user_email").focus();
		    return;
		}
		if ( pass.match(/\S/) == null ){
		    signUp.passError = true;
		    $("#user_pass").focus();
		    return;
		}
		if ( conf.match(/\S/) == null ){
		    signUp.passError = true;
		    return;
		}
		if ( univ == "Your University" ){
		    signUp.univError = true;
		    $("[name='university_value']").focus();
		    return;
		}
		// Check to see if email is valid
		if ( email.search("@") == -1 || email.search(/\./) == -1 ){
		    signUp.emailError = true;
		    signUp.errorMessage = "Please enter a valid '.edu' address";	    
		    $("#user_email").focus();
		    return;
		}
		// Check to see if the email is a .edu email
		if ( email.match(/\.edu$/) == null ){
		    signUp.emailError = true;
		    signUp.errorMessage = "Please enter a '.edu' email address";
		    $("#user_email").focus();
		    return;
		}
		// Check to see if pass == confirm_pass
		if ( pass != conf ){
		    signUp.confError = true;
		    signUp.errorMessage = "Password and confirm password fields do not match";
		    $("#user_pass,#user_pass_confirm").val("");
		    $("#user_pass").focus();
		    return;
		}
		var post = {"email":email,"password":pass,"university":univ,"ajax":true};
	    }
	    else {
		var post = {"email":info.email,"university":info.university,"ajax":true};
	    }
	    $http({
		method: 'POST',
		url: "./php/register.php",
		data: $.param(post),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    }).then(callback);
	},
	login : function(emailSelector,passSelector,$scope,callback){
	    // Set defaults
	    $scope.emailError = false;
	    $scope.passError = false;
	    
	    var email = $(emailSelector).val();
	    var pass = $(passSelector).val();
	    if ( email.match(/\S/) == null ){
		$scope.emailError = true;
		return;
	    }
	    if ( pass.match(/\S/) == null ){
		$scope.passError = true;
		return;
	    }
	    $http({
		method: 'POST',
		url: "../../webfiles/login/login/login.php",
		data: $.param({"email":email,"password":pass,"landingPage":true}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    }).then(callback);
	},
	fbSignUp : function(p,success,error){
	    if ( !p ){
		$window.hello.login("facebook",{"scope":"email","display":"popup"},function(r){
		    if ( r.error ){
			if ( error )
			    error(r);
			return;
		    }
		    $window.hello(r.network).api("/me").success(success);
		});
		return;
	    }
	    // Register the user
	    if ( p.education ){
		// Find the college(s) that they go to. If it's one we have in our database, sign them up with that college!
		var colleges = [];
		for ( var i = p.education.length-1; i > -1; i-- ){
		    if ( p.education[i].type == "College" ){
			colleges.push(p.education[i].school.name);
		    }
		}
		if ( colleges.length == 0 ){
		    $scope.signUp.errorMessage = "We could not determine the college that you go to. Please use our sign up form.";
		    $("#user_email").val(p.email);
		    angular.element($("#sign_up_button")[0]).triggerHandler("click");
		}
		else {
		    // Verify that we have that college in the database
		    $http({
			method: 'POST',
			url: "./backend/checkForCollege.php",
			data: $.param({"colleges":JSON.stringify(colleges)}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		    }).then(success);
		}
	    }
	},
	fbLogin : function(success,error){
	    $window.hello.login("facebook",{"scope":"email","display":"popup"},function(r){
		if ( r.error ){
		    if ( error )
			error(r);
		}
		$window.hello(r.network).api("/me").success(function(p){
		    if ( !p.email ){
			$scope.errorMessage = "An error occurred while using Facebook to login. Please use our login form.";
			$("#user_email_login").val(p.email);
			angular.element($("#login_button")[0]).triggerHandler("click");
		    }
		    else {
			$http({
			    method: 'POST',
			    url: "./php/verifyUser.php",
			    data: $.param({"email":p.email}),
			    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(success);
		    }
		});
	    });
	}
    }
}]);
