String.prototype.replaceAll = function(find, replace) {
    return this.replace(new RegExp(find, "g"), replace);
};

var API_PATH = "api/"

var app = angular.module("plMatch", []);
app.controller(
    "MatchController", ['$scope', '$http', '$window', '$timeout', '$sce',
        'register', 'common',
        function($scope, $http, $window, $timeout, $sce, register, common) {

            var savedResources = {};
            var limitResultsTo = 10;
            var delimLength = 0;
            var autoSelecting = false; // When true, the (de)select all checkbox was clicked

            $scope.homePage = $window.homePage;
            $scope.emailError = false;
            $scope.passError = false;

            $scope.signUp = {
                emailError: false,
                passError: false,
                univError: false,
                confError: false,
                errorMessage: "",

                reset: function() {
                    signUp.emailError = false;
                    signUp.passError = false;
                    signUp.univError = false;
                    signUp.confError = false;
                    signUp.errorMessage = "";
                }
            };

            $scope.errorMessage = "";
            $scope.resultsPage = {
                advisors: 1,
                courses: 1,
                theses: 1,
                grants: 1
            };

            $scope.selectAll = "Unselect All"; // All delimiters are checked by default
            $scope.display = "advisors";

            $scope.results = {
                advisors: [],
                courses: [],
                theses: [],
                grants: []
            };
            $scope.maxPages = 0;
            $scope.delims = {
                departments: [],
                funding: [],
                thesis: []
            };
            $scope.departments = [];
            $scope.resultsLength = {};
            $scope.testDrive = $window.testDrive;
            $scope.common = common;

            // Login and Registration Function

            $scope.login = function(email, pass) {
                register.login(email, pass, $scope, loginComplete);
            };

            $scope.register = function(social, info) {
                register.register(social, info, $scope,
                    registrationComplete);
            };

            $scope.fbSignUp = function(p) {
                register.fbSignUp(p, fbSignUpSuccess, fbSignUpError);
            };

            $scope.fbLogin = function() {
                register.fbLogin(fbLoginSuccess, fbLoginError);
            };

            // Login/Registration callbacks
            var fbLoginError = function(r) {
                $scope.$apply(function() {
                    $scope.errorMessage =
                        "An error occurred while using Facebook to login. Please use our login form.";
                });
                angular.element($("#login_button")[0]).triggerHandler(
                    "click");
                return;
            };

            var fbLoginSuccess = function(response) {
                if (response.data == "registered") {
                    // GREAT! Let's let them continue using the site
                    $scope.testDrive = false;
                    $window.testDrive = false;
                    if (($("#search_box").val().match(/\S/) !== null) &&
                        ($window.homePage !== true))
                        $scope.search();
                    else if ($window.homePage === true)
                        $window.location = "match.php";
                } else if (response.data == "new user") {
                    $scope.fbSignUp(p);
                }
            };

            var fbSignUpError = function() {
                $scope.$apply(function() {
                    $scope.errorMessage =
                        "An error occurred while using Facebook to login. Please use our login form.";
                });
                angular.element($("#login_button")[0]).triggerHandler(
                    "click");
                return;
            };

            var fbSignUpSuccess = function(response) {
                if (response.data.length === 0) {
                    $scope.signUp.errorMessage =
                        "We could not determine the college that you go to. Please use our sign up form.";
                    $("#user_email").val(p.email);
                    angular.element($("#sign_up_button")[0]).triggerHandler(
                        "click");
                } else if (response.data.length == 1) {
                    $scope.register(true, {
                        "email": p.email,
                        "university": response.data[0]
                    });
                } else if (response.data.length > 1) {
                    // Have the user select a university
                    var selected = false;
                    var index = 0;
                    while (selected === false && response.data[index] !==
                        null) {
                        if (confirm(
                                "Would you like to sign up to Project Lever as attending " +
                                response.data[index] + "?")) {
                            $scope.register(true, {
                                "email": p.email,
                                "university": response.data[
                                    index]
                            });
                            selected = true;
                            break;
                        } else {
                            index++;
                        }
                    }
                    if (selected === false) {
                        $scope.signUp.errorMessage =
                            "You must select a university that you are attending to use Project Lever. Please use the form below.";
                        $("#user_email").val(p.email);
                        angular.element($("#sign_up_button")[0]).triggerHandler(
                            "click");
                    }
                }
            };

            var loginComplete = function(response) {
                if (response.data.search("login successful") > -1) {
                    $scope.testDrive = false;
                    $window.testDrive = false;
                    if ($("#search_box").val().match(/\S/) !== null &&
                        $window.homePage !== true)
                        $scope.search();
                    else if ($window.homePage === true)
                        $window.location = "match.php";
                } else if (response.data.search("register") > -1 ||
                    response.data.search("\/login\/register") > -1) {
                    // Tell them to register
                    $scope.emailError = true;
                    $scope.errorMessage =
                        "We could not locate a user with that email.";
                    $(passSelector).val("");
                } else if (response.data.search("Incorrect password") >
                    -1) {
                    $scope.passError = true;
                    $scope.errorMessage =
                        "Email/password combination is incorrect.";
                    $(passSelector).val("");
                }
            };

            var registrationComplete = function(response) {
                if (response.data == "complete") {
                    $scope.testDrive = false;
                    $window.testDrive = false;
                    if ($("#search_box").val().match(/\S/) !== null &&
                        $window.homePage !== true)
                        $scope.search();
                    else if ($window.homePage === true)
                        $window.location = "match.php";
                } else if (response.data == "no school") {
                    $window.location =
                        "http://www.projectlever.com/webfiles/login/register/school.html";
                } else if (response.data == "registered") {
                    $timeout(function() {
                        $("#user_email_login").val(email);
                        $scope.errorMessage =
                            "You've already registered!";
                        angular.element($("#login_button")[0]).triggerHandler(
                            "click");
                    }, 0);
                } else if (response.data == "invalid email") {
                    signUp.emailError = true;
                    signUp.errorMessage = "Invalid email address";
                }
            };

            var getResultSet = function(data, callback) {
                $http({
                    method: 'GET',
                    url: "./php/get_result_page.php?data=" +
                        JSON.stringify(data) +
                        "&sid=" + Math.random()
                }).then(callback);
            };

            $scope.displayHTML = function(html) {
                // Possible security vulnerability
                return $sce.trustAsHtml(html);
            };

            $scope.getNumResults = function(type) {
                return $scope.results[type + "NumResults"];
            };

            $scope.getResultSet = function(type) {
                return $scope.results[type];
}

            $scope.getPages = function() {
                var out = [];
                var curPage = $scope.resultsPage[$scope.display];
                $scope.maxPages = Math.ceil($scope.results[$scope.display +
                    "NumResults"] / limitResultsTo);
                for (var i = (curPage - 5 > 0 ? curPage - 5 : 1), n = (
                        curPage + 6 >= 10 ? curPage + 6 : (11 >
                            $scope.maxPages ? $scope.maxPages : 11)
                    ); i < n && i < $scope.maxPages; i++)
                    out.push(i);
                return out;
            };

            $scope.toggle = function(department) {
                if (department !== null) {
                    var attrName = department.replace(/\s/g, "_");
                    if (_.indexOf($scope.delims.departments, department) >
                        -1) {
                        // Check to see if all of the checkboxes are checked
                        if ($("[name='department_names']:checked").length ==
                            delimLength) {
                            $scope.selectAll = "Unselect All";
                            $("#select_all").prop("checked", false);
                        }
                    } else {
                        $scope.selectAll = "Select All";
                        $("#select_all").prop("checked", false);
                    }
                    if (autoSelecting === false)
                        $scope.changePage(1);
                } else {
                    $scope.changePage(1);
                }
            };

            $scope.toggle = function(department) {
                if (department !== null) {
                    var attrName = department.replace(/\s/g, "_");
                    if (_.indexOf($scope.delims.departments, department) >
                        -1) {
                        // Check to see if all of the checkboxes are checked
                        if ($("[name='department_names']:checked").length ==
                            delimLength) {
                            $scope.selectAll = "Unselect All";
                            $("#select_all").prop("checked", false);
                        }
                    } else {
                        $scope.selectAll = "Select All";
                        $("#select_all").prop("checked", false);
                    }
                    if (autoSelecting === false)
                        $scope.changePage(1);
                } else {
                    $scope.changePage(1);
                }
            }

            $scope.checkAll = function() {
                var selectAll = false;
                if ($scope.selectAll == "Select All") {
                    var selector =
                        "[name='department_names']:not(:checked)";
                    selectAll = true;
                } else if ($scope.selectAll == "Unselect All") {
                    var selector = "[name='department_names']:checked";
                    selectAll = false;
                } else
                    return;

                angular.forEach($(selector), function(cb, index) {
                    autoSelecting = true;
                    $timeout(function() {
                        $(cb).trigger("click");
                    }, 50);
                });
                // This function checks if angular is still (un)checking the checkboxes
                function check() {
                    if (selectAll === true) {
                        if ($("[name='department_names']:not(:checked)")
                            .length > 0) {
                            // Keep waiting
                            $timeout(check, 50);
                            return;
                        }
                    } else if (selectAll === false) {
                        if ($("[name='department_names']:checked").length >
                            0) {
                            // Keep waiting
                            $timeout(check, 50);
                            return;
                        }
                    }
                    // Finished!
                    autoSelecting = false;
                    $scope.changePage(1);
                }
                check();
            }
            $scope.changePage = function(n) {
                $("#" + $scope.display + "_results,#pagination_buttons")
                    .hide();
                $(".loading-gif").show();
                $("body").scrollTop(0);
                $timeout(function() {
                    $scope.resultsPage[$scope.display] = n;
                    getResultSet({
                        display: $scope.display,
                        page: n,
                        delimiters: $scope.delims,
                        limit: limitResultsTo
                    }, function(response) {
                        console.log(response);
                        if ($scope.display ==
                            "advisors") {
                            $scope.results.advisors =
                                response.data.Advisor.results;
                        } else if ($scope.display ==
                            "courses") {
                            $scope.results.courses =
                                response.data.Course.results;
                        } else if ($scope.display ==
                            "theses") {
                            $scope.results.theses =
                                response.data.Thesis.results;
                        } else if ($scope.display ==
                            "funding" || $scope.display ==
                            "grants") {
                            $scope.results.grants =
                                response.data.Grant.results;
                        }
                        $scope.results.advisorsNumResults =
                            response.data.Advisor.results_length;
                        $scope.results.coursesNumResults =
                            response.data.Course.results_length;
                        $scope.results.thesesNumResults =
                            response.data.Thesis.results_length;
                        $scope.results.grantsNumResults =
                            response.data.Grant.results_length;
                        $timeout(function() {
                            $("#" + $scope.display +
                                "_results,#pagination_buttons"
                            ).show();
                            $(".loading-gif").hide();
                        }, 500);
                    });
                }, 200);
            };
            $scope.getEmail = function getEmail(email) {
                var json = $window.JSON.parse(email);
                if (json === null)
                    return email;
                else
                    return json["0"];
            };

            $scope.search = function search() {
            	$("#results_container,#pagination_buttons").hide();
                $(".loading-gif").show();
                $("#thesis_delim:checked,#funding_delim:checked").trigger(
                    "click");
                $scope.resultsPage = {
                    advisors: 1,
                    courses: 1,
                    extracurriculars: 1,
                    theses: 1,
                    grants: 1
                };
                $http({
                    method: 'POST',
                    url: API_PATH +"matchResults.php",
                    data: $.param({
                        "input": $("#search_box").val(),
                        "session": $window.session_id,
                        "page": $scope.resultPage,
                        "test_drive": $scope.testDrive
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function(response) {
                    console.log(response);
                	// alert(JSON.stringify(response));
                    // Loop through and find departments
                    var temp = {
                        out: []
                    };
                    var tempList = {};
                    for (var type in response.data.results) {
                    	// alert("There are types!");
                        var data = response.data.results[type];
                        for (var i = 0, n = data.length; i < n; i++) {
                            if ($scope.testDrive === false)
                                var deptName = data[i].department;
                            else
                                var deptName = data[i].university;
                            // Get the correct type name
                            switch (type) {
                                case "Advisor":
                                    {
                                        var typeName =
                                            "advisors";
                                        break;
                                    }
                                case "Course":
                                    {
                                        var typeName =
                                            "courses";
                                        break;
                                    }
                                case "Thesis":
                                    {
                                        var typeName = "theses";
                                        break;
                                    }
                                case "Grant":
                                    {
                                        var typeName = "grants";
                                    }
                                case "Extracurricular":
                                	{
                                		var typeName = "extracurriculars";
                                	}
                            }

                            // Push the department to the temporary department list. It's temporary so that Angular doesn't try to
                            // update as it's building
                            if (deptName !== null && temp[
                                    deptName] !== true) {
                                if (deptName.search("-") > -1) {
                                    var dnames = deptName.split(
                                        "-");
                                    for (var j = dnames.length -
                                            1; j > -1; j--) {
                                        // Remove leading and trailing spaces, tabs, and indents
                                        dnames[j] = dnames[j].replace(
                                            /^[\s\n\r\t]{0,}/,
                                            "").replace(
                                            /[\s\n\r\t]{0,}$/,
                                            "");
                                        if (temp[dnames[j]] !==
                                            true && dnames[j] !==
                                            "") {
                                            temp.out.push(
                                                dnames[j]);
                                            temp[dnames[j]] =
                                                true;
                                            if ($window._.find(
                                                    $scope.delims
                                                    .departments,
                                                    function(
                                                        dept) {
                                                        return
                                                        dept ==
                                                            dnames[
                                                                j
                                                            ];
                                                    }) !== null) {
                                                $scope.delims.departments
                                                    .push(
                                                        dnames[
                                                            j]);
                                            }
                                        }
                                    }
                                } else {
                                    temp.out.push(deptName);
                                    temp[deptName] = true;
                                    $scope.delims.departments.push(
                                        deptName);
                                }
                            }
                        }
                    }
                    $scope.resultsLength = response.data.result_count;
                    $scope.departments = temp.out;
                    delimLength = temp.out.length;
                    $scope.results.advisorsNumResults =
                        response.data.result_count.Advisor.total;
                    $scope.results.coursesNumResults = response
                        .data.result_count.Course.total;
                    $scope.results.thesesNumResults = response.data
                        .result_count.Thesis.total;
                    $scope.results.grantsNumResults = response.data
                        .result_count.Grant.total + response.data
                        .result_count.Funding.total;
                  	$scope.results.extracurricularsNumResults = response
                        .data.result_count.Extracurricular.total;

                    $scope.results.advisors = response.data.results
                        .Advisor || [];
                    $scope.results.courses = response.data.results
                        .Course || [];
                    $scope.results.theses = response.data.results
                        .Thesis || [];
                    $scope.results.extracurriculars = response.data.results
                        .Extracurricular || [];
                    $scope.results.grants = response.data.results
                        .Grants || [];
                    $scope.results.fundings = response.data.results
                        .Funding || [];
                    $scope.currentResults = $scope.results.advisors;
                    $scope.currentCount = $scope.results.advisorsNumresults;
                    console.log($scope.results.fundings);
                    // Combine the grant and funding results for now
                    $scope.results.grants =  $scope.results.grants.concat( 
                        $scope.results.fundings);
                    $timeout(function() {
                        $(
                            "#results_container,#pagination_buttons"
                        ).show();
                        $(
                            ".loading-gif,#match_page_intro"
                        ).hide();
                    }, 500);
                });
                };


            $scope.isSavedResource = function isSavedResource(id, type) {
                type = type.toLowerCase();
                if (type == "advisors")
                    type = "advisor";
                else if (type == "courses") {
                    type = "course";
                } else if (type == "theses") {
                    type = "thesis";
                } else if (type == "grants") {
                    type = "grant";
                } else if (type == "extracurriculars") {
                	type ="extracurricular";
                }
                if (savedResources[type]) {
                    return $window._.indexOf(savedResources[type], id) >
                        -1;
                }
                $scope.currentSearch = $scope.results.advisors
                return false;
            };
            
        $scope.showResource = function showResource(type) {
                type = type.toLowerCase();
                if (type == "advisors"){
                    $scope.currentResults = $scope.results.advisors;
                    $scope.currentCount = $scope.results.advisorsNumresults;
                 }
                else if (type == "courses") {
                    $scope.currentResults = $scope.results.courses;
                    $scope.currentCount = $scope.results.coursesNumresults;
                } else if (type == "theses") {
                   $scope.currentResults = $scope.results.theses;
                    $scope.currentCount = $scope.results.thesesNumresults;
                } else if (type == "grants") {
                    $scope.currentResults = $scope.results.grants;
                    $scope.currentCount = $scope.results.grantsNumresults;
                } else if (type == "extracurriculars") {
                    $scope.currentResults = $scope.results.extracurriculars;
                    $scope.currentCount = $scope.results.extracurricularsNumresults;
                }
                //var resources;
                //var results = $scope.results[$scope.display +
                //    "NumResults"];
                //alert(results);
                //$scope.display = type;
                //if ((resources = $("#" + type + "_results")).length > 0 &&
                //    results > 0) {
                //    $scope.display = type;
                //    $scope.changePage(1);
                
            };
            $scope.toggleFavorite = function toggleFavorite(id, type) {
                type = type.toLowerCase();
                if (type == "advisors")
                    type = "advisor";
                else if (type == "courses") {
                    type = "course";
                } else if (type == "theses") {
                    type = "thesis";
                } else if (type == "grants") {
                    type = "grant";
                } else if (type == "extracurriculars") {
                	type ="extracurricular";
                }
                if ($scope.isSavedResource(id, type)) {
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
                        if (response.data == "success") {
                            savedResources[type] = $window._.reject(
                                savedResources[type],
                                function(testId) {
                                    return testId == id;
                                });
                        }
                    });
                } else {
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
                        if (response.data == "success") {
                            if (!savedResources[type])
                                savedResources[type] = [];
                            savedResources[type].push(id);
                        }
                    });
                }
            };
            $scope.alphaExists = function alphaExists(desc) {
                if (desc != undefined && desc != null)
                    return (desc.match(/[a-z]/i) != null);
                else
                    return false;
            };
            $scope.snippet = function snippet(desc) {
                desc.replace(/^\s*Description\s*|/, "").replace(/|/g,
                    " ");
                if (desc.length > 70)
                    return desc.substring(0, 67) + "...";
                else
                    return desc;
            };

            // Initializing code
            if ($window.initQuery != undefined) {
                $(".search-bar").html(initQuery);
                $scope.search();
            }
            // Set the enter key to search
            $("#search_box").on("keydown", function() {
                var e = window.event;
                var u = e.charCode ? e.charCode : e.keyCode;
                if (u == 13) {
                    var form;
                    if ((form = $("#test_drive")).length > 0)
                        form[0].submit();
                    else
                        $scope.search();
                    //  e.preventDefault ? e.preventDefault() : e.preventDefault();
                    //   e.stopPropagation? e.stopPropagation() : e.cancelBubble();
                    return false;
                }
            });
            // Get the saved resources
            $http({
                method: 'POST',
                url: "./php/get_saved_resources.php",
            }).then(function(response) {
                savedResources = response.data;
            });
        }
    ]).directive('autoGrow', function() {
    return function(scope, element, attr) {
        var max = attr["autoGrow"];
        var minHeight = element[0].offsetHeight;
        var lines = 1;
        var maxLines = -1; // Default means that the number of lines is unlimited
        var defHeight = element.height();
        paddingLeft = element.css('paddingLeft');
        paddingRight = element.css('paddingRight');
        element.css("min-height", element.outerHeight());
        $("#search_button,.search-button").css({
            "max-height": element.css("min-height"),
            "font-size": element.css("min-height").replace(
                "px", "") * 1 - 10 + "px"
        });

        var $shadow = angular.element(
            '<div id="auto_grow_shadow"></div>').css({
            position: 'absolute',
            top: -10000,
            left: -10000,
            width: element.width(),
            fontSize: element.css('fontSize'),
            fontFamily: element.css('fontFamily'),
            lineHeight: element.css('lineHeight'),
            resize: 'none',
            "font-weight": element.css('fontWeight'),
            "text-decoration": element.css('textDecoration'),
            padding: element.css("padding"),
            "word-wrap": "break-word",
        });
        // Check for a maximum height or max number of lines
        if (max != "") {
            maxLines = Number(max);
            element.css({
                "max-height": maxLines * element.outerHeight() +
                    "px",
                "overflow-y": "auto"
            });
        }
        angular.element(document.body).append($shadow);

        var update = function() {
            var times = function(string, number) {
                for (var i = 0, r = ''; i < number; i++) {
                    r += string;
                }
                return r;
            };
            var val = element.val().replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/&/g, '&amp;')
                .replace(/\n$/, '<br/>&nbsp;')
                .replace(/\n/g, '<br/>');
            $shadow.html(val);

            lines = $shadow.height() / parseInt(element.css(
                "lineHeight"), 10);
            if (lines <= maxLines) {
                element.css({
                    'height': Math.max($shadow.height(),
                        minHeight) + 'px',
                    'overflow': 'hidden'
                });
            } else {
                element.css("overflow-y", "auto");
            }
        };
        element.bind({
            'blur': function() {
                element.scrollTop(0).css({
                    "height": defHeight,
                    "overflow": "hidden"
                });
            },
            'focus': function() {
                element.height($shadow.height());
                if (lines >= maxLines)
                    element.css("overflow-y", "auto");
            },
            'keyup keydown keypress change': update
        });
        update();
    };
}).directive('checkList', function() {
    return {
        scope: {
            list: '=checkList',
            value: '@'
        },
        link: function(scope, elem, attrs) {
            function handler(setup) {
                var checked = elem.prop('checked');
                var index = scope.list.indexOf(scope.value);

                if (checked && index == -1) {
                    if (setup) elem.prop('checked', false);
                    else scope.list.push(scope.value);
                } else if (!checked && index != -1) {
                    if (setup) elem.prop('checked', true);
                    else scope.list.splice(index, 1);
                }
            }

            var setupHandler = handler.bind(null, true);
            var changeHandler = handler.bind(null, false);

            elem.on('change', function() {
                scope.$apply(changeHandler);
            });
            scope.$watch('list', setupHandler, true);
        }
    };
});
