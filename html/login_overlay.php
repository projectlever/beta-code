<link type="text/css" href="css/register.css" rel="stylesheet"/>
<script type="text/javascript" src="js/register_overlay.js"></script>
<!-- SIGN UP FORM  -->
<div id="reg_form" class="layer" name="float-form" onclick="closeForm()" ng-if="testDrive == true">
  <div class="container full-width full-height center" style="margin-top:100px">
    <div class="row">
      <div class="col-xs-6 col-xs-offset-3 reg_form">
	<div id="pop_up_registration" class="pop_up_form">
	  <div class="reg_form">
	    <div id="sign_up_form">
	      <table style="width:100%">
		<tbody>
		  <tr style="text-align:center">
		    <td>
		      <h5>
			Sign up with
		      </h5>
		    </td>
		    <td style="border:1px solid #aaa"><!-- This is here merely for the "OR" circle XD --></td>
		    <td>
		      <h5>
			Sign up with email
		      </h5>
		    </td>
		  </tr>
		  <tr>
		    <td><!-- LEAVE BLANK --></td>
		    <td><!-- LEAVE BLANK --></td>
		    <td style="text-align:center;border-left:2px solid #aaa">
		      <span ng-show="signUp.errorMessage != ''">{{signUp.errorMessage}}</span>
		    </td>
		  </tr>
		  <tr>
		    <td style="width:50%;" valign="top">
		      <ul id="social_list">
			<li>
			  <a class="btn btn-facebook" ng-click="fbSignUp()">
			    <i class="icon-facebook"></i>Facebook
			  </a>
			</li>
		      </ul>
		    </td>
		    <td style="position:relative;border:1px solid #aaa">
		      <div class="or_circle">OR</div>
		    </td>
		    <td style="width:50%;">
		      <table id="form_elements">
			<tr>
			  <td style="border-bottom:1px solid #aaa;border-top:1px solid #aaa">
			    <input ng-class="{'border-error':signUp.emailError==true}" style="border:none;color:#aaa;box-shadow:none;" placeholder="Email@your-school.edu" type="text" id="user_email" name="user_email" size="10" />
			  </td>
			</tr>
			<tr>
			  <td style="border-bottom:1px solid #aaa;">
			    <input ng-class="{'border-error':signUp.passError==true,'border-error':signUp.confError==true}" style="border:none;color:#aaa;box-shadow:none;" type="password" id="user_pass" name="user_pass" size="10" placeholder="Password" />
			  </td>
			</tr>
			<tr>
			  <td style="border-bottom:1px solid #aaa;">
			    <input ng-class="{'border-error':signUp.confError==true}" style="border:none;color:#aaa;box-shadow:none;" type="password" id="user_pass_confirm" name="user_pass_confirm" size="10" placeholder="Confirm Password" />
			  </td>
			</tr>
			<tr>
			  <td style="border-bottom:1px solid #aaa">
			    <select name="university_value" class="reg_select" ng-class="{'border-error':signUp.univError==true}">
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(0);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(0);">Your University</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(1);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(1);">Harvard University</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(2);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(2);">MIT</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(3);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(3);">Johns Hopkins University</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(4);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(4);">Tufts University</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(5);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(5);">Columbia University</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(6);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(6);">University of Notre Dame</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(6);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(7);">Yeshiva University</option>
			      <option onclick="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(7);" ontouchend="$('#university_dropdown_visual').val(this.innerHTML);$('#university_value').val(8);">Can't find your school?</option>
			    </select>
			  </td>
			</tr>
			<tr>
			  <td style="padding-top:15px;padding-bottom:1em;">
			    <button class="pl_button" style="box-shadow:none;position:relative;top:2px;text-align:center;font-size:1em" id="submit_button" ng-click="register()">Sign Up</button>
			    <!-- <a href="./webfiles/login/login/password.html" id="forgot_pass_link">Forgot your password?</a> -->
			  </td>
			</tr>
		      </table>    
		    </td>
		  </tr>
		</tbody>
	      </table>
	    </div>
	  </div>
	</div>
      </div>
    </div>
  </div>
</div>
<!-- SIGN IN FORM -->
<div id="sign_in_form" class="layer" name="float-form" onclick="closeForm()" ng-if="testDrive == true">
  <div class="container full-width full-height center" style="margin-top:100px">
    <div class="row">
      <div class="col-xs-6 col-xs-offset-3 reg_form">
	<div id="pop_up_registration" class="pop_up_form">
	  <div class="reg_form">
	    <div id="sign_up_form">
	      <table style="width:100%">
		<tbody>
		  <tr style="text-align:center">
		    <td>
		      <h5>
			Log in with
		      </h5>
		    </td>
		    <td style="border:1px solid #aaa"><!-- This is here merely for the "OR" circle XD --></td>
		    <td>
		      <h5>
			Log in with email
		      </h5>
		    </td>
		  </tr>
		  <tr>
		    <td>
		      <!-- LEAVE BLANK -->
		    </td>
		    <td>
		      <!-- LEAVE BLANK -->
		    </td>
		    <td style="border-left:2px solid #aaa;text-align:center">
		      <span id="error_message" ng-show="errorMessage != ''">{{errorMessage}}</span>
		    </td>
		  </tr>
		  <tr>
		    <td style="width:50%;" valign="top">
		      <ul id="social_list">
			<li>
			  <a class="btn btn-facebook" ng-click="fbLogin()">
			    <i class="icon-facebook"></i>Facebook
			  </a>
			</li>
		      </ul>
		    </td>
		    <td style="position:relative;border:1px solid #aaa">
		      <div class="or_circle" style="top:3.5em">OR</div>
		    </td>
		    <td style="width:50%;">
		      <table id="form_elements">
			<tr>
			  <td style="border-bottom:1px solid #aaa;border-top:1px solid #aaa">
			    <input ng-class="{'border-error':emailError==true}" style="border:none;color:#aaa;box-shadow:none;" type="text" id="user_email_login" name="user_email" size="10" placeholder="Email" />
			  </td>
			</tr>
			<tr>
			  <td style="border-bottom:1px solid #aaa;">
			    <input ng-class="{'border-error':passError == true}" type="password" style="border:none;color:#aaa;box-shadow:none;" id="user_pass_login" name="user_pass" size="10" placeholder="Password" />
			  </td>
			</tr>
			<tr>
			  <td style="padding-top:15px;padding-bottom:1em;">
			    <button class="pl_button" style="box-shadow:none;position:relative;top:2px;text-align:center;font-size:1em" id="submit_button" ng-click="login('#user_email_login','#user_pass_login')">Log In</button>
			    <p style="margin-top:1em">
			      <a href="./webfiles/login/login/password.html" id="forgot_pass_link">Forgot your password?</a>
			    </p>
			  </td>
			</tr>
		      </table>    
		    </td>
		  </tr>
		</tbody>
	      </table>
	    </div>
	  </div>
	</div>
      </div>
    </div>
  </div>
</div>
