// Analytics
var analytics=analytics||[];analytics.load=function(e){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src=("https:"===document.location.protocol?"https://":"http://")+"d2dq2ahtl5zl1z.cloudfront.net/analytics.js/v1/"+e+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n);var r=function(e){return function(){analytics.push([e].concat(Array.prototype.slice.call(arguments,0)))}},i=["identify","track","trackLink","trackForm","trackClick","trackSubmit","pageview","ab","alias","ready","group"];for(var s=0;s<i.length;s++)analytics[i[s]]=r(i[s])};
analytics.load("l0mz54o6kb");

$.post("./webfiles/login/checkLogin.php",{}).done(function(data){
	if ( data == "logged in" ){
		window.location = "./profile.php";
		return;
	}
});

// Research Ideas
var idea = "Type in anything you want, like...";
var ideas = new Array(
	"I'm interested in the migration patterns of starfish (ahem...Sea Stars). Marine biology, shellfish (read: sushi), and environmental politics are some of my passions.",
	"What role did the phrase 'Let them eat cake' have in the French Revolution? What social, economic, and culinary conditions were necessary for such events to unfold?",
	"Tone-deafness, musical psychology, occipital lobe compensation, and why people are so bad at Karaoke",
	"My thesis will be a complex analysis of the literary devices that differentiate the styles of famous Roman poets such as Catullus, Ovid, and Horace. I want to research the use of synecdoche, chiasmus, and metonymy and how they relate to other big literary terms.",
	"I'm hoping to build a water filtering device that can be used in third world countries at little or no cost. H2O! H2O!",
	"Mathematical modeling of financial mergers and acquisitions, and how many times the big company ate the little company.",
	"Shady Science in Cinema: An analysis of the effect that Breaking Bad, Numbers, and The Core had on American educational outcomes",
	"Can Hamsters Count?: A shocking connection drawn between the fields of applied mathematics and organismic and evolutionary biology.",
	"'Putin' Pressure on Russia: A investigation of John Kerry's role as Secretary of State in the Middle East.",
	"My research is about optimizing control flow in computer programs and why debugging is so hard.",
	"Implied tonal (but totally weird) harmonies in the operas and songs of Berg, Schoenberg, and Webern.",
	"Public health, tuberculosis, politics, policy, Russia, democracy (or lack thereof)",
	"Ambiguous sentences and why they confuse linguists more than most people.",
	"Thermodynamics, fluid mechanics, and excellent coffee brewing",
	"Modern art, finger painting, and the technical differences between the two"
);
var randomNumber = getRandomInt(0,ideas.length - 1);
idea = idea + '"' + ideas[randomNumber] + '"';
function getRandomInt (min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Check sessions
var loggedin = false;
$.post("session_check.php",{}).done(function(data){
	if(data == "redirect"){
		loggedin = true;
	}
	else
		loggedin = false;
});

window.fbAsyncInit = function() {
	FB.init({
		appId      : '164434750353945', // App ID
		channelUrl : '//www.projectlever.com/channel.html', // Channel File
		status     : true, // check login status
		cookie     : true, // enable cookies to allow the server to access the session
		xfbml      : true  // parse XFBML
	});
	/*FB.getLoginStatus(function(response) {
	// this will be called when the roundtrip to Facebook has completed
	}, true);*/
	(function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
}
// Here we run a very simple test of the Graph API after login is successful. 
// This onlogin() function is only called when the login is successful.
function fbLogin(){
	FB.login(function(response){
		if (response.status == "not_authorized" || response.status == "unknown"){
			$("#social_login_error").css("visibility","visible");
		}
		else {
			onlogin();
		}
	},{scope: 'email,user_education_history'});
}
var email = "";
function onlogin() {
	analytics.track('Facebook Attempt', {
	});
	var universities = {
		"Columbia University" : 5,
		"University of Notre Dame" : 6,
		"Tufts University" : 4,
		"Johns Hopkins University" : 3,
		"Harvard University" : 1,
		"MIT" : 2,
		"Massachusetts Institute of Technology" : 2,
		"Massachusetts Institute of Technology (MIT)" : 2,
		names : ["Columbia University","University of Notre Dame","Tufts University", "Johns Hopkins University", "Harvard University", "MIT", "Massachusetts Institute of Technology", "Boston University"]
	};  	  
	FB.api("/me",function(response){
		if (response.email!=null){
			$("#social_login_error").css("visibility","hidden");
			// This checks to see if we have access the the FB user's email
			// Use the email to verify that they are registered with out system
			$.post("./webfiles/login/verifyUser.php",{email:response.email}).done(function(data){
				if (data == "registered"){
					// If the user is registered, then send them to the profile page
					analytics.track('Facebook Log in Success',{
				
					},function(){
						window.location = "profile.php";
					});
				}
				else if (data == "new user"){
					// This is a new user. Check to see if we have access to their education history. If we do
					// find the college that they go to, verify that is their college, and then register them in
					// the data  	
					if (response.education != null){
						var education = response.education;
						var college = "";
						for (var i = 0, n = education.length; i < n; i++){
							if (education[i].type == "College"){
								college = education[i].school.name;
								i = n;
							}
						}
						if (response.email!=null){
							email = response.email; 
							if (universities[college]!=null){
								register($('#reg_select')[0].options[universities[college]].innerHTML,'facebook');	
							}
							else {
								window.location = "./webfiles/login/register/index.php?e=1";
							}
						}
						else {
							$("#social_login_error").css("visibility","visible");
						}
					}
					else {
						window.location = "./webfiles/login/register/index.php?e=1";
					}  	
				}
			});
		}
		else {
			$("#social_login_error").css("visibility","visible");
		}  	  	
	});
}  	
function login(){
	var email = $("#user_email").val();
	var pass = $("#user_pass").val();
	$("#sign_up_form_float_loading_sign").css("display","table");
	$.post("./webfiles/login/login/login.php",{"email":email,"password":pass,"landingPage":"true"}).done(function(data){
                console.log(data);
		if (data.search("login successful")>-1){
			analytics.track('Log in Success',{
			},{},function(){
			   window.location = "profile.php";
			});
		}
		else if(data.search("closed") > -1){
			window.location = 'connectLater.html';
		}
		else if (data.search("Incorrect password")>-1){
			alert("The email-password combination is incorrect. Please verify your information and try again or try logging in with Facebook.");
			$("#sign_up_form_float_loading_sign").css("display","none");
			$("#user_pass").bind("keyup",function(e){
				 keyCheck(e,this);
			});
			return false;
		}
                else if (data.search("haven't registered")>-1 || data.search("\/login\/register")>-1){
                        alert("You haven't registered yet.");
                        window.location = "./webfiles/login/register/";
                }
                else {
                     console.log("Other value:" + data);
                }
		return false;
	 });
	return;
}
function formTransition(n,m){
	if ($("[name="+(n-1)+"]").length>0&&m==null){
		$("[name="+(n-1)+"]").css({left:"-500px"});
	}
	if ($("[name="+(n)+"]").length>0){
		$("[name="+(n)+"]").css({left:"0px"});
	}
	if (m!=null){
		$("[name="+(m)+"]").css({left:"-500px"});
	}
	return;
}
function register(university,password,social){ 
	$("#sign_up_form_float_loading_sign").css("display","table");
	$("#reg_select").css("border-color","transparent");
	$("#user_email").css("border-color","transparent");
	$("#user_pass").css("border-color","transparent");
	$("#user_pass_confirm").css("border-color","transparent");
	if (university!=null){
		// What happens when university == "Can't find your university?"
		if ( university === "Can't find your school?" ){
			window.location = "/webfiles/login/register/school.html";
		}
		if (password!=null){
			var post = {"email":email,"university":university.replace(/\s/g,"_"),"ajax":"true","password":password};
		}
		else {
			var post = {"email":email,"university":university.replace(/\s/g,"_"),"ajax":"true"};
		}
		$.post("./webfiles/login/register/register.php",post).done(function(data){
			//alert(data);exit;
			if (data == "complete"){
				if (social == 'facebook'){
					analytics.track('Facebook Register Success',{
					},{},function(){
						window.location = "http://www.projectlever.com/edit_profile.php";
					});
				}
				else {
					analytics.track('Register Success',{
					},{},function(){
					   window.location = "http://www.projectlever.com/edit_profile.php";
					});
				}
			}
			else if (data == "invalid email"){
				$("#user_email").css("border-color","#f00");
				alert("Please enter a valid email address");
				$("#sign_up_form_float_loading_sign").css("display","none");
			}
			else if (data == "no school"){
				window.location = "http://www.projectlever.com/webfiles/login/register/school.html";
			}
			else if (data == "registered"){
				if (!password){
					var post = {"email":email,"fb_login":"true"};
				}
				else {
					var post = {"email":email,"password":password};
				}	  	
				alert("You've already registered. Please login.");
				showRegistrationForm("login");
			}
		});
	}
	else {
		// Check that the passwords match
		if ($("#user_pass").val()==$("#user_pass_confirm").val()){
			var pass = $("#user_pass").val();
			var validEmail = /\S+@\S+\.\S+/;
			email = $("#user_email").val();
			// Check that the email is valid
			if (validEmail.test(email)){
				// Check that they entered a school
				var college = $("#reg_select")[0].options[$("#reg_select")[0].selectedIndex].innerHTML;
				if (college!="Your University"){
					register(college,pass);
				}
				else {
					$("#reg_select").css("border-color","#f00");
					alert("Please select a school.");
					$("#sign_up_form_float_loading_sign").css("display","none");
				}
			}
			else {
				$("#user_email").css("border-color","#f00");
				alert("Please enter a valid email address.");
				$("#sign_up_form_float_loading_sign").css("display","none");
			}
		}
		else {
			$("#user_pass").css("border-color","#f00");
			$("#user_pass_confirm").css("border-color","#f00");
			$("#user_pass,#user_pass_confirm").val("");
			alert("Passwords do not match.");
			$("#sign_up_form_float_loading_sign").css("display","none");
		}
	}
}
function keyCheck(e,el){
	var u = e.charCode ? e.charCode : e.keyCode;
	if (u == 13){
		el.onkeyup = {};
		login();
	}
	return false;
}
var fieldOfStudy = "";
var researchBlob = "";	
function transition(n){
	if (n == 1){
		$("#university_dropdown").css({
			"visibility":"hidden",
			"display":"none"
		});
		$("#welcome_message>p").html("Tell us about your research!");
		$("#welcome_message>p").css({
			"display":"block",
			"opacity":"1"
		});
		$("#welcome_message").css("border-bottom","0.05em solid #aaa");
		$("#searching_sign").css("display","none");
		$("#field_of_study").val(fieldOfStudy==""?"What is your field of study?":fieldOfStudy);
		$("#field_of_study")[0].onkeypress = function(e){cancelSubmit(e)};
		$("#field_of_study").css("opacity","1");
		$("#research_blob").css({
			"display":"block",
			"visibility":"visible",
			"opacity":"1"
		});	
		$("#research_blob").val(researchBlob==""?"What is your research about?":researchBlob);
		$("#research_blob")[0].onkeypress = function(e){cancelSubmit(e)};
		$("#match_results_table").attr("valign","middle");
		$("#match_results").css({
			"display":"block",
			"opacity":"1"
		});
		$("#topBox").css("display","none");
		$("#ib_button").html("Try it");
		$("#ib_button").css("opacity","1");
		$("#back_button").css("display","none");
		$(".ib_box").css("opacity","1");
		$("#ib_button")[0].onclick = function(){
			transition(2);
		};
		$("#ib_button")[0].ontouchend = function(){
			transition(2);
		};	
	}
	else if (n == 2){
		fieldOfStudy = $("#field_of_study").val();
		$("#topBox").css("display","block");
		if (fieldOfStudy == 'What is your field of study?'){
			fieldOfStudy = "";
		}
		researchBlob = $("#research_blob").val();
		if ($("#research_blob")[0].name.search("1")>-1){
			researchBlob = "";
		}
		var totalInput = fieldOfStudy+" "+researchBlob;
		if (totalInput == " "){
			alert("Please enter your field of study and a description about your research.");
			return;
		}
		$("[name='hide']").css("opacity","0");
		$("#welcome_message>p").css("opacity","0");
		$("#welcome_message>p").css("display","none");
		$("#field_of_study").css("opacity","0");
		$("#research_blob").css("opacity","0");
		$("#ib_intro").css("opacity","0");
		$(".ib_box").css("opacity","0");
		$("#welcome_message").css({
			"opacity":"1",
			"border":"none"
		});
		$("#welcome_message>p").css({
			"display":"none",
		});	
		$("#searching_sign").css({
			"display":"block",
			"visibility":"visible"
		});	
		$("#searching_sign").html("Searching...");	
		var flash = setInterval(function(){	
			$("#searching_sign").css("opacity","1");	
			setTimeout(function(){
				$("#searching_sign").css("opacity","0");
			},500);
		},1005);	
		$.post("./advisors/magic4.php",{input:totalInput}).done(function(data){
			// Find the rankings of advisors, grants, and research projects
			analytics.track('Tried it out',{
				"Field of Study" : fieldOfStudy,
				"Research" : researchBlob
			});
			
			// Take the JSON information and place the information into the proper HTML form
			data = JSON.parse(data);
			console.log(data);
			var Advisor     = data.Advisor;
			var Grant       = data.Grant;
			var Thesis      = data.Thesis;
			var Course      = data.Course;
			
			// Add in the advisor information
			document.getElementById("advisor_name").innerHTML = (Advisor.name ? Advisor.name : "Sorry, no advisors were found") + (Advisor.description ? "<br/><span class='data'>" + ((Advisor.description == "") ? "": Advisor.description) + "</span>" : "");
			document.getElementById("advisor_university").innerHTML = "<span class='data'>" + (Advisor.university ? Advisor.university.replace(/_/g," ") : "") + (Advisor.school ? " - " + Advisor.school : "") + (Advisor.department ? " - " + Advisor.department : "") + "</span>";
			$("#advisor_seal").attr({
				src     : Advisor.crest,
				alt     : (Advisor.university ? Advisor.university.replace(/_/g," ") : ""),
				title   : (Advisor.university ? Advisor.university.replace(/_/g," ") : "")
				//style   : "height:"+$("#advisor_seal").parent().height()+"px"
			});
			//document.getElementById("advisor_school").innerHTML = Advisor[0].school;
			//document.getElementById("advisor_department").innerHTML = Advisor[0].department;
			
			// Add in the course information
			document.getElementById("course_name").innerHTML = (Course.name ? Course.name : "Sorry, no courses were found");
			document.getElementById("course_university").innerHTML = "<span class='data'>" + (Course.university ? Course.university.replace(/_/g," ") + " - " : "") + (Course.school ? Course.school : "") + (Course.department ? " - " + Course.department : "") + "</span>";
			$("#course_seal").attr({
				src     : Course.crest,
				alt     : (Course.university ? Course.university.replace(/_/g," ") : ""),
				title   : (Course.university ? Course.university.replace(/_/g," ") : "")
				//style   : "height:"+imageHeight+"px"
			});
			//document.getElementById("course_school").innerHTML = Course[0].school;
			//document.getElementById("course_department").innerHTML = Course[0].department;
			
			// Add in the thesis information
			document.getElementById("thesis_name").innerHTML = (Thesis.name ? Thesis.name : "Sorry, no theses were found");
			document.getElementById("thesis_university").innerHTML =  "<span style='font-size:0.8em;color:#000'>" + (Thesis.author ? "By " +  Thesis.author : "") + (Thesis.advisor ? ", advised by Prof. " + Thesis.advisor : "") + "</span><br/><span class='data'>" + (Thesis.university ? Thesis.university.replace(/_/g," ") : "") + (Thesis.school ? " - " + Thesis.school: "") + (Thesis.department ? " - " + Thesis.department : "") + "</span>";
			$("#thesis_seal").attr({
				src     : Thesis.crest,
				alt     : (Thesis.university ? Thesis.university.replace(/_/g," ") : ""),
				title   : (Thesis.university ? Thesis.university.replace(/_/g," ") : "")
				//style   : "height:"+imageHeight+"px"
			});
			//document.getElementById("thesis_school").innerHTML = Thesis[0].school;
			//document.getElementById("thesis_department").innerHTML = Thesis[0].department;
			
			// Add in the grant information
			document.getElementById("grant_name").innerHTML = (Grant.name ? Grant.name : "Sorry, no grants were found") + (Grant.university ? " - " + Grant.university.replace(/_/g," ") : "");
			$("#grant_seal").attr({
				src     : Grant.crest,
				alt     : (Grant.university ? Grant.university.replace(/_/g," ") : ""),
				title   : (Grant.university ? Grant.university.replace(/_/g," ") : "")
				//style   : "height:"+imageHeight+"px"
			});
			
			clearTimeout(flash);
			$("#message").css("opacity","0");	
			setTimeout(function(){
				// Display all hidden elements
				$("#university_dropdown").css({
					"visibility":"visible",
					"display":"block"
				});
				$("#welcome_message>p").html("Tell us more about yourself:");
				$("#welcome_message").css("border-bottom","0.05em solid #aaa");
				$("#welcome_message>p").css("display","block");
				$("#searching_sign").css("display","none");
				$("#welcome_message>p").css("opacity","1");
				$("#field_of_study").val("Your email");
				$("#field_of_study")[0].onkeypress = function(){};
				$("#research_blob").css("display","none");
				$("#field_of_study").css("opacity","1");
				$("#research_blob").css("visibility","hidden");
				$("#research_blob")[0].onkeypress = function(e){};
				$("#searching_sign").css("visibility","hidden");
				$("#match_results_table").attr("valign","none");
				$("#match_results").css("display","block");
				$("#match_results").css("opacity","1");	
				$("#ib_button").html("Next");
				$("#back_button").css("display","inline-block");
				$("#ib_button").val("Next");
				$("#ib_button").css("opacity","1");
				$("#ib_intro").css("display","none");	
				$(".ib_box").css("opacity","1");
				$("#ib_button")[0].onclick = function(){
					$("#user_input")[0].submit();
				};
				$("#ib_button")[0].ontouchend = function(){
					$("#user_input")[0].submit();
				};	
				$("#match_results").css("z-index","3");
				$("[name='hide']").css("opacity","1");	
			},500);	
			return;
		});	
	}
}
function displayVideo(){
	var e = window.event;
	e.stopPropagation ? e.stopPropagation() : e.cancelBubble(); 
	$("#video_background").css({
		"visibility":"visible",
		"display":"block"
	});
	$("#video_screen").css({	
		"left" : ($(window).width()/2)-320+"px",
		"top"	: ($(window).height()/2)-(385/2)+"px",
		"visibility":"visible",
		'display':'block'
	});	
	return false;
}
function displayDropDown(){
	$("#university_dropdown").css("display","block");
	var e = window.event;
	e.stopPropagation ? e.stopPropagation() : e.cancelBubble();
	e.preventDefault ? e.preventDefault() : null;
	$(document).click(function(){
		closeDropDown();
	});
	return;
}
function closeDropDown(){
	$("#university_dropdown").css("display","none");
	return;
}
var player;
function onYouTubeIframeAPIReady(){
	player = new YT.Player('video_screen');
	return;
}
function setUp(){
	$("#content").css({
		width : Math.round(($(".navbar").width()/$(window).width())*100)+"%",
		height : ($("#footer").position().top-$(".sourrounding_wrapper").height()-$(".sourrounding_wrapper").position().top)/$(window).height()*100+"%",
		top : Math.round((($(".sourrounding_wrapper").position().top+$(".sourrounding_wrapper").height())/$(window).height())*100)+"%",
	});
	$(".reg_form").css({
		"left" : ($(window).width()/2)-250+"px",
		"top" : ($(window).height()/2)-150+"px",
	});
	$('#reg_form_close_button').css({
		left : (($(window).width()/2)-250)+$("#sign_up_form_float").width()+$("#reg_form_close_button").width()-5+"px",
		top : (($(window).height()/2)-150)-$("#reg_form_close_button").height()-13+"px"
	});
	$("#display_results > tbody > tr").click(function(){
		window.location = "./webfiles/login/register/";
	});
}	
function showRegistrationForm(type){
    $ = jQuery;
	$("#sign_up_form_float_loading_sign").css("display","none");
	if (type == 'login'){
		$("#forgot_pass_link").css("display","block");
		$("[name=forgot_pass_break]").css("display","block");
		$("#form_login_label").html("Log in with your email");
		$("#fb_login_button").children("span").html("Login with Facebook");
		$("#social_media_label").html("Log in with...");
		$('#pop_up_registration').css('display','block');
		$('#reg_form_close_button').css('display','block');
		$("#dropdown_container").css('display','none');
		$($('#user_pass_confirm')[0].parentNode).css('display','none');
		$('#u_drop').css('display','none');
		$("#submit_button").html("Log in");
		$("#submit_button").click(function(){
			login();
		});	
		$("#edx_sign > a").attr("href","http://projectlever.com/EdX/login.php");
		analytics.track('Log in Attempt',{
			
		});
	}
	else if (type=="register"){
		$("#forgot_pass_link").css("display","none");
		$("[name=forgot_pass_break]").css("display","none");
		$("#form_login_label").html("Sign up with your email");
		$("#fb_login_button").children("span").html("Sign up with Facebook");
		$("#social_media_label").html("Sign up with...");
		$('#pop_up_registration').css('display','block');
		$('#reg_form_close_button').css('display','block');
		$("#dropdown_container").css('display','block');
		$($('#user_pass_confirm')[0].parentNode).css('display','table-cell');
		$('#u_drop').css('display','block');
		$("#submit_button").html("Sign Up");
		$("#submit_button").click(function(){
			register(null,null);
		}).bind("touchend",function(){
			register(null,null);
		});
		$("#edx_sign > a").attr("href","http://projectlever.com/EdX/register.php");
		analytics.track('Registration Attempt',{
			
		});	
	}
	return;
}	
function popUpClick(){
	window.event.stopPropagation?window.event.stopPropagation():window.event.cancelBubble();
	$("#pop_up_registration").css("display","none");
	$("#reg_form_close_button").css("display","none");
	return;
}
function cancelSubmit(e){
	var u = e.charCode ? e.charCode : e.keyCode;
	if (u == 13){
	   e.preventDefault ? e.preventDefault() : null;
	   transition(2);
	   return false;
	}
	return;
}
