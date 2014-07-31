var testDrive = false;
var app = angular.module("plAdvisor", []).controller('mainController', ['$scope', '$http', '$location', '$window', '$sce', 'common', '$timeout', 'resourceMatch',
    function($scope, $http, $location, $window, $sce, common, $timeout, resourceMatch) {
        var savedResources = {};
        var loadedSources = 0;
        var sourcesNeeded = 2;
        $scope.pageType = $window.pageType;
	$scope.pageId   = $scope.id = $window.advisorId;
        $scope.vizDataExists = false;
        $scope.data = {};
        $scope.selected = "bio";
        $scope.matchPage = true;
        $scope.common = common;
        $scope.results = {};
        $scope.singleDisplay = true;
        $scope.display = "advisors";
        if ($window.loggedIn == false) {
            $scope.testDrive = true;
        } else {
            $scope.testDrive = false;
        }
        $scope.displayDelimiters = false;

        var sourceLoaded = $scope.sourceLoaded = function() {
            loadedSources++;
            if (loadedSources == sourcesNeeded) {
                $("#loading_sign").hide();
            }
        }

        // Methods
	$scope.resourceExists = function(){
	    return $("#similar_resources").children().length > 0;
	}
        $scope.select = function(thing) {
            $scope.selected = thing;
        }
        $scope.getHTML = $scope.displayHTML = function(html) {
            if (html)
                return $sce.trustAsHtml(html.replace(/description/i, ""));
            return "";
        }
        $scope.stripTags = function(html) {
            return String(html).replace(/<[^>]+>/gm, '');
        }
        $scope.getEmail = function(email) {
            return email;
        }
        $scope.toggleFavorite = function toggleFavorite(id, type) {
	    type = type.toLowerCase();
            if (type == "advisors" || type == "Advisor") type = "advisor";
            else if (type == "courses" || type == "course" || type == "Course") {
                type = "course";
            } else if (type == "theses" || type == "thesis" || type == "Thesis") {
                type = "thesis";
            } else if (type == "grants" || type == "grant" || type == "Grant") {
                type == "grant";
            }
            if ($scope.isSavedResource(id, type)) {
                $("#star_load").css("display", "inline");
                $http({
                    method: 'POST',
                    url: "./php/save_remove_resource.php",
                    data: $.param({
                        "id": id,
                        "type": type,
                        "saved": "Remove"
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function(response) {
                    $("#star_load").css("display", "none");
                    console.log(savedResources);
                    console.log([id, type]);
                    if (response.data == "success") {
                        savedResources[type.toLowerCase()] = $window._.reject(savedResources[type], function(testId) {
                            return testId == id;
                        });
                    }
                });
            } else {
                $("#star_load").css("display", "inline");
                $http({
                    method: 'POST',
                    url: "./php/save_remove_resource.php",
                    data: $.param({
                        "id": id,
                        "type": type,
                        "saved": "Save"
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function(response) {
                    $("#star_load").css("display", "none");
                    if (response.data == "success") {
                        if (type == "advisors") type = "advisor";
                        else if (type == "courses") {
                            type = "course";
                        } else if (type == "theses") {
                            type = "thesis";
                        } else if (type == "grants") {
                            type = "grant";
                        }
			if ( !savedResources[type] )
			    savedResources[type] = [];
                        savedResources[type].push(String(id));
                    }
                });
            }
        }
        $scope.isSavedResource = function isSavedResource(id, type) {
	    type = type.toLowerCase();
            if (type == "advisors" || type == "Advisor")
                type = "advisor";
            else if (type == "courses" || type == "Course") {
                type = "course";
            } else if (type == "theses" || type == "Thesis") {
                type = "thesis";
            } else if (type == "grants") {
                type = "grant";
            }
            if (savedResources[type]) {
                if (typeof id != "string")
                    id += "";
                return $window._.indexOf(savedResources[type], id) > -1;
            }
            return false;
        }
        $scope.verifyImage = function(string) {
            if (string.match(/\<img/) != null) {
                var img = string.substr(string.search(/src\=[\"\']{1}/));
                return img.substr(0, img.search(/[\"\']/));
            } else
                return string;
        }
	$scope.log = function(msg){
	    console.log(msg);
	}
        $scope.login = function(emailSelector, passSelector) {
            // Set defaults
            $scope.emailError = false;
            $scope.passError = false;

            var email = $(emailSelector).val();
            var pass = $(passSelector).val();
            if (email.match(/\S/) == null) {
                $scope.emailError = true;
                return;
            }
            if (pass.match(/\S/) == null) {
                $scope.passError = true;
                return;
            }
            $http({
                method: 'POST',
                url: "../../webfiles/login/login/login.php",
                data: $.param({
                    "email": email,
                    "password": pass,
                    "landingPage": true
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(response) {
                console.log(response);
                if (response.data.search("login successful") > -1) {
                    window.location.reload();
                } else if (response.data.search("register") > -1 || response.data.search("\/login\/register") > -1) {
                    // Tell them to register
                    $scope.emailError = true;
                    $scope.errorMessage = "We could not locate a user with that email."
                    $(passSelector).val("");
                } else if (response.data.search("Incorrect password") > -1) {
                    $scope.passError = true;
                    $scope.errorMessage = "Email/password combination is incorrect.";
                    $(passSelector).val("");
                }
            });
        }
        $scope.register = function(social, info) {
            if (!social || social == false) {
                var email = $("#user_email").val();
                var pass = $("#user_pass").val();
                var conf = $("#user_pass_confirm").val();
                var univ = $("[name='university_value']")[0].options[$("[name='university_value']")[0].selectedIndex].innerHTML;
                signUp.reset();
                // Check for empty fields
                if (email.match(/\S/) == null) {
                    signUp.emailError = true;
                    $("#user_email").focus();
                    return;
                }
                if (pass.match(/\S/) == null) {
                    signUp.passError = true;
                    $("#user_pass").focus();
                    return;
                }
                if (conf.match(/\S/) == null) {
                    signUp.passError = true;
                    return;
                }
                if (univ == "Your University") {
                    signUp.univError = true;
                    $("[name='university_value']").focus();
                    return;
                }
                // Check to see if email is valid
                if (email.search("@") == -1 || email.search(/\./) == -1) {
                    signUp.emailError = true;
                    signUp.errorMessage = "Please enter a valid '.edu' address";
                    $("#user_email").focus();
                    return;
                }
                // Check to see if the email is a .edu email
                if (email.match(/\.edu$/) == null) {
                    signUp.emailError = true;
                    signUp.errorMessage = "Please enter a '.edu' email address";
                    $("#user_email").focus();
                    return;
                }
                // Check to see if pass == confirm_pass
                if (pass != conf) {
                    signUp.confError = true;
                    signUp.errorMessage = "Password and confirm password fields do not match";
                    $("#user_pass,#user_pass_confirm").val("");
                    $("#user_pass").focus();
                    return;
                }
                var post = {
                    "email": email,
                    "password": pass,
                    "university": univ,
                    "ajax": true
                };
            } else {
                var post = {
                    "email": info.email,
                    "university": info.university,
                    "ajax": true
                };
            }
            $http({
                method: 'POST',
                url: "./php/register.php",
                data: $.param(post),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(response) {
                console.log(response);
                signUp.reset();
                if (response.data == "complete") {
                    $scope.testDrive = false;
                    $window.testDrive = false;
                    if ($("#search_box").val().match(/\S/) != null)
                        $scope.search();
                } else if (response.data == "no school") {
                    window.location = "http://www.projectlever.com/webfiles/login/register/school.html";
                } else if (response.data == "registered") {
                    $timeout(function() {
                        $("#user_email_login").val(email);
                        $scope.errorMessage = "You've already registered!";
                        angular.element($("#login_button")[0]).triggerHandler("click");
                    }, 0);
                } else if (response.data == "invalid email") {
                    signUp.emailError = true;
                    signUp.errorMessage = "Invalid email address";
                }
            });
        }
        $scope.fbSignUp = function(p) {
            if (!p) {
                $window.hello.login("facebook", {
                    "scope": "email",
                    "display": "popup"
                }, function(r) {
                    if (r.error) {
                        $scope.$apply(function() {
                            $scope.errorMessage = "An error occurred while using Facebook to login. Please use our login form.";
                        });
                        angular.element($("#login_button")[0]).triggerHandler("click");
                        return;
                    }
                    $window.hello(r.network).api("/me").success(function(p) {
                        $scope.fbSignUp(p);
                    });
                });
                return;
            }
            // Register the user
            if (p.education) {
                // Find the college(s) that they go to. If it's one we have in our database, sign them up with that college!
                var colleges = [];
                for (var i = p.education.length - 1; i > -1; i--) {
                    if (p.education[i].type == "College") {
                        colleges.push(p.education[i].school.name);
                    }
                }
                if (colleges.length == 0) {
                    $scope.signUp.errorMessage = "We could not determine the college that you go to. Please use our sign up form.";
                    $("#user_email").val(p.email);
                    angular.element($("#sign_up_button")[0]).triggerHandler("click");
                } else {
                    // Verify that we have that college in the database
                    $http({
                        method: 'POST',
                        url: "./backend/checkForCollege.php",
                        data: $.param({
                            "colleges": JSON.stringify(colleges)
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    }).then(function(response) {
                        if (response.data.length == 0) {
                            $scope.signUp.errorMessage = "We could not determine the college that you go to. Please use our sign up form.";
                            $("#user_email").val(p.email);
                            angular.element($("#sign_up_button")[0]).triggerHandler("click");
                        } else if (response.data.length == 1) {
                            $scope.register(true, {
                                "email": p.email,
                                "university": response.data[0]
                            });
                        } else if (response.data.length > 1) {
                            // Have the user select a university
                            var selected = false;
                            var index = 0;
                            while (selected == false && response.data[index] != null) {
                                if (confirm("Would you like to sign up to Project Lever as attending " + response.data[index] + "?")) {
                                    $scope.register(true, {
                                        "email": p.email,
                                        "university": response.data[index]
                                    });
                                    selected = true;
                                    break;
                                } else {
                                    index++;
                                }
                            }
                            if (selected == false) {
                                $scope.signUp.errorMessage = "You must select a university that you are attending to use Project Lever. Please use the form below.";
                                $("#user_email").val(p.email);
                                angular.element($("#sign_up_button")[0]).triggerHandler("click");
                            }
                        }
                    });
                }
            }
        }
        $scope.fbLogin = function() {
            $window.hello.login("facebook", {
                "scope": "email",
                "display": "popup"
            }, function(r) {
                if (r.error) {
                    $scope.$apply(function() {
                        $scope.errorMessage = "An error occurred while using Facebook to login. Please use our login form.";
                    });
                    angular.element($("#login_button")[0]).triggerHandler("click");
                    return;
                }
                $window.hello(r.network).api("/me").success(function(p) {
                    if (!p.email) {
                        $scope.errorMessage = "An error occurred while using Facebook to login. Please use our login form.";
                        $("#user_email_login").val(p.email);
                        angular.element($("#login_button")[0]).triggerHandler("click");
                    } else {
                        $http({
                            method: 'POST',
                            url: "./php/verifyUser.php",
                            data: $.param({
                                "email": p.email
                            }),
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        }).then(function(response) {
                            if (response.data == "registered") {
                                // GREAT! Let's let them continue using the site
                                $scope.testDrive = false;
                                $window.testDrive = false;
                                if ($("#search_box").val().match(/\S/) != null)
                                    $scope.search();
                            } else if (response.data == "new user") {
                                $scope.fbSignUp(p);
                            }
                        });
                    }
                });
            });
        }
	$scope.foundSimilarResources = function(response){
	    console.log(response);
	    if ( !$scope.data.similar )
		$scope.data.similar = [];
	    var sim = {};
	    if ( $scope.pageType != "Advisor" ){
		sim[$scope.pageType] = response[$scope.pageType];
		$scope.data.similar.push(sim);   
	    }
	    else {
		$scope.results.advisors = response.Advisor;
	    }
	}
        $scope.toType = function(obj) {
                return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
            }
            // Init by grabbing advisor info
        $http({
            method: 'POST',
            url: "./php/get_info.php",
            data: $.param({
                'id': $window.advisorId,
                'type': $scope.pageType
            }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }).then(function(response) {
	    console.log(response);
            $scope.data = response.data;
            $scope.id = response.data[pageType][pageType + "_ID"];

            // Get similar resources
            resourceMatch.match($scope, $http, $scope.pageId, pageType);

            // Set up the visualization
            $scope.vizDataExists = response.data.vizDataExists;
            if ($scope.vizDataExists == true) {
                // This loop is REQUIRED. Otherwise, d3 tries to access #viz before it loads....This waits for #viz to load before running d3load
                function testForElement() {
                    if ($("#viz").length > 0) {
                        d3load($window.advisorId, response.data.weights, 700);
                        sourceLoaded();
                    } else {
                        $timeout(testForElement, 500);
                    }
                }
                $timeout(testForElement, 500);
            } else {
                sourceLoaded();
            }
        });
        // Get saved resources
        $http({
            method: 'POST',
            url: "./php/get_saved_resources.php",
        }).then(function(response) {
            for (var prop in response.data) {
                var type = prop.toLowerCase();
                if (type == "advisors") type = "advisor";
                else if (type == "courses") {
                    type = "course";
                } else if (type == "theses") {
                    type = "thesis";
                } else if (type == "grants") {
                    type == "grant";
                }
                savedResources[type] = response.data[prop];
            }
	    console.log(savedResources);
            sourceLoaded();
        });
        if ($window.loggedIn == false) {
            setTimeout(function() {
                $window.showForm('sign_in_form');
            }, 150);
        }
    }
]);
