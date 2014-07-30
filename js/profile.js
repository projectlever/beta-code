var app = angular.module('profile',[]).controller('ProfileController',['$scope','$window','$http','$sce','common','$timeout',function($scope,$window,$http,$sce,common,$timeout){
    var savedResources = {};
    // View controlling variables
    $scope.profilePage = true; // This is used to tell the navbar to show 'Logout' instead of 'Profile'
    $scope.user = {}; // User information
    $scope.selected = 'savedResources'; // Default selected tab
    $scope.matchTextEdited = false; // If the student's match text was edited
    $scope.displayDelimiters = false; // Show the delimiters?
    $scope.results = {}; // Saved results
    $scope.display = "advisors";
    $scope.testDrive = $window.testDrive;
    $scope.savesEdited = false;
    $scope.editMode = ($window.editMode == 1); // Did we enter in edit mode?
    $scope.departments = [];
    $scope.schools = [];

    $scope.common = common;
    $scope.isSavedResource = function(){
	return true;	 
    }
    $scope.setEditMode = function(bool){
	$scope.editMode = bool;
    }
    $scope.show = function(school){
	return true;
    }
    function validate(){
	var name = $("#user_name").val();
	var dept = $("#department_select option:selected").text();
	var sch  = $("#school_select option:selected").text();
	var email = $("#email").val();
	if ( name == "" ){
	    alert("Please enter your name");
	    $("#user_name").focus();
	    return false;
	}
	if ( dept.search("Select Department") > -1 ){
	    alert("Please select your department");
	    $("#department_select").focus();
	    return false;
	}
	if ( sch.search("Select School") > -1 ){
	    alert("Please select your school");
	    $("#school_select").focus();
	    return false;
	}
	var emailTest = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	if ( email == "" ||  $scope.common.alphaExists(email) == false ){
	    alert("Please enter your email address");
	    $("#email").focus();
	    return false;
	}
	else if ( emailTest.test(email) == false ){
	    alert("Please enter a valid email address");
	    $("#email").focus().val("");	    
	    return false;
	}
	else if ( email.match(/\.edu$/) == null ){
	    alert("Please enter a .edu email");
	    $("#email").focus();
	    $scope.formError.email = true;
	}
	// Check for valid cv file type
	var cvFile = $("#cv_display").val();
	if ( cvFile != "" && $scope.common.alphaExists(cvFile) == true ){
	    // Get the file extension. Allow only .pdf, .docx, .doc, and .txt
	    var extFilter = /\.pdf$|\.docx$|\.doc$|\.txt$/i;
	    if ( extFilter.test(cvFile) == false ){
		alert("CV's can only be PDFs, DOC, DOCX, or TXT");
		return false;
	    }
	}
	// Check for valid image type
	var image = $("#profile_image_button").val();
	if ( image != "" && $scope.common.alphaExists(image) == true ){
	    var imgFilter = /\.jpg$|\.png$|\.gif$|\.jpeg$|\.bmp$/i;
	    if ( imgFilter.test(image) == false ){
		alert("Profile images can only by of type JPG, JPEG, PNG, or GIF");
		return false;
	    }
	}	
	return true;
    }
    $scope.saveMatchText = function(){
	if ( $scope.matchTextEdited == false )
	    return;
	$http({
	    method : "POST",
	    url : "php/save_match_text.php",
	    data: $.param({"text":$(".search-bar").val(),"id":$scope.user.id}),
	    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function(response){
	    if ( response.data == "saved" ){
		$scope.matchTextEdited = false;
	    }
	});
    }
    $scope.schoolIsSelected = function(school){
    	return school[0] == $scope.user.school;
    }
    $scope.isSelected = function(name,type,user){
    	if ( user == undefined )
    	    return "";
	if ( user.search(name) > -1 ){
	    $("#"+name.replace(/\s/g,"_")).attr("selected",true);
	}
    }
    $scope.displayHTML = function(html){
	return $sce.trustAsHtml(html);
    }
    $scope.showResource = function(resource){
	$scope.display = resource;
    }
    $scope.login = function(emailSelector,passSelector){
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
	}).then(function(response){
	    console.log(response);
	    if ( response.data.search("login successful") > -1 ){
		window.location.reload();
	    }
	    else if ( response.data.search("register") > -1 || response.data.search("\/login\/register") > -1 ){
		// Tell them to register
		$scope.emailError = true;
		$scope.errorMessage = "We could not locate a user with that email."
		$(passSelector).val("");
	    } 
	    else if ( response.data.search("Incorrect password") > -1 ){
		$scope.passError = true;
		$scope.errorMessage = "Email/password combination is incorrect.";
		$(passSelector).val("");
	    }
	});
    }
    $scope.register = function(social,info){
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
	}).then(function(response){
	    console.log(response);
	    signUp.reset();
	    if ( response.data == "complete" ){		
		$scope.testDrive = false;
		$window.testDrive = false;
		if ( $("#search_box").val().match(/\S/) != null )
		    $scope.search();		
	    }
	    else if ( response.data == "no school" ){
		window.location = "http://www.projectlever.com/webfiles/login/register/school.html";
	    }
	    else if ( response.data == "registered" ){
		$timeout(function(){
		    $("#user_email_login").val(email);
		    $scope.errorMessage = "You've already registered!";
		    angular.element($("#login_button")[0]).triggerHandler("click");
		},0);
	    }
	    else if ( response.data == "invalid email" ){
		signUp.emailError = true;
		signUp.errorMessage = "Invalid email address";
	    }
	});
    }
    $scope.fbSignUp = function(p){
	if ( !p ){
	    $window.hello.login("facebook",{"scope":"email","display":"popup"},function(r){
		if ( r.error ){
		    $scope.$apply(function(){
			$scope.errorMessage = "An error occurred while using Facebook to login. Please use our login form.";
		    });
		    angular.element($("#login_button")[0]).triggerHandler("click");
		    return;
		}
		$window.hello(r.network).api("/me").success(function(p){
		    $scope.fbSignUp(p);
		});
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
		}).then(function(response){
		    if ( response.data.length == 0 ){
			$scope.signUp.errorMessage = "We could not determine the college that you go to. Please use our sign up form.";
			$("#user_email").val(p.email);
			angular.element($("#sign_up_button")[0]).triggerHandler("click");
		    }
		    else if ( response.data.length == 1 ){
			$scope.register(true,{"email":p.email,"university":response.data[0]});
		    }
		    else if ( response.data.length > 1 ){
			// Have the user select a university
			var selected = false;
			var index = 0;
			while ( selected == false && response.data[index] != null ){
			    if ( confirm("Would you like to sign up to Project Lever as attending " + response.data[index]+"?") ){
						    $scope.register(true,{"email":p.email,"university":response.data[index]});
				selected = true;
				break;
			    }
			    else {
				index++;
			    }
			}
			if ( selected == false ){
			    $scope.signUp.errorMessage = "You must select a university that you are attending to use Project Lever. Please use the form below.";
			    $("#user_email").val(p.email);
			    angular.element($("#sign_up_button")[0]).triggerHandler("click");
			}
		    }
		});
	    }
	}
    }
    $scope.fbLogin = function(){
	$window.hello.login("facebook",{"scope":"email","display":"popup"},function(r){
	    if ( r.error ){
		$scope.$apply(function(){
		    $scope.errorMessage = "An error occurred while using Facebook to login. Please use our login form.";
		});
		angular.element($("#login_button")[0]).triggerHandler("click");
		return;
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
		    }).then(function(response){
			if ( response.data == "registered" ){
			    // GREAT! Let's let them continue using the site
			    $scope.testDrive = false;
			    $window.testDrive = false;
			    if ( $("#search_box").val().match(/\S/) != null )
				$scope.search();
			}
			else if ( response.data == "new user" ){
			    $scope.fbSignUp(p);
			}
		    });
		}
	    });
	});
    }
    $scope.toggleFavorite = function toggleFavorite(id,type){
	if ( type == "advisors" )
	    type = "advisor";
	else if ( type == "courses" ){
	    type = "course";
	}
	else if ( type == "theses" ){
	    type = "thesis";
	}
	else if ( type == "grants" ){
	    type = "grant";
	}
	if ( $scope.isSavedResource(id,type) ){
	    $http({
		method: 'POST',
		url: "./php/save_remove_resource.php",
		data: $.param({"id":id,"type":type,"saved":"Remove"}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    }).then(function(response){
		if ( response.data == "success" ){
		    savedResources[type] = $window._.reject(savedResources[type],function(testId){
			return testId == id;
		    });
		}
	    });
	}
	else {
	    $http({
		method: 'POST',
		url: "./php/save_remove_resource.php",
		data: $.param({"id":id,"type":type,"saved":"Save"}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    }).then(function(response){
		console.log(response);
		if ( response.data == "success" ){
		    savedResources[type].push(id);
		}
	    });
	}	    
    }
    $scope.isSavedResource = function isSavedResource(id,type){
	if ( type == "advisors" )
	    type = "advisor";
	else if ( type == "courses" ){
	    type = "course";
	}
	else if ( type == "theses" ){
	    type = "thesis";
	}
	else if ( type == "grants" ){
	    type = "grant";
	}
	if ( savedResources[type] ){
	    return $window._.indexOf(savedResources[type],id) > -1;
	}
	return false;
    }
    // If not logged in...show the login form!
    if ( $window.loggedIn == false ){
	setTimeout(function(){
	    $window.showForm('sign_in_form');
	},150);
    }
    else {
	// Get user information
	$http({
	    method: "GET",
	    url: "php/get_user_info.php"
	}).then(function(response){
	    console.log(response);
	    $scope.user    = response.data;
	    $scope.results.advisors = response.data.saved.Advisor;
	    $scope.results.courses  = response.data.saved.Course;
	    $scope.results.theses   = response.data.saved.Thesis;
	    $scope.results.grants   = response.data.saved.Funding;
	    console.log($scope.results);
	    $timeout(function(){
		$scope.onPageLoad();
		$("#save_form").show();
	    },250);
	});
	$http({
	    method: "GET",
	    url: "php/get_unique.php"
	}).then(function(response){
	    console.log(response);
	    $scope.schools = response.data.schools;
	    $scope.departments = response.data.departments;
	});
	// Element set up
	
	// Display only departments from the selected school.
	$("#school_select").on("change",function(){
	    var school = $("#school_select > option:selected").text().replace(/^[\s\n\t\r]{0,}/,"").replace(/[\n\r\s\t]{0,}$/,"");
	    $("#department_select > option:selected").removeAttr("selected");
	    $scope.user.department = "";
	    // Use $apply because this function isn't called by the controller. Anytime a function is called outside of the controller, you MUST use $apply
	    $scope.$apply(function(){
		// Trim the white space, new lines, and tabs from the option text and set it as the selected school
		$scope.user.school = school;
	    });
	});
	
	// Validate the save form before submitting
	$("#save_form").on("submit",function(){
	    return validate();
	});
    }
    // Get the saved resources
    $http({
	method: 'POST',
	url: "./php/get_saved_resources.php",
    }).then(function(response){
	savedResources = response.data;
    });
    // Initialize hello.js
    $window.hello.init({
	"facebook":"164434750353945"
    });
}]);
