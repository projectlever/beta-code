<div class="pl-content pl-zebra" id="results_container">
    <div class="layer" id="results_layer">
      <div class="container full-width full-height">
	<div class="row full-height" style="padding-top:2em">
	  <div class="col-xs-2 col-xs-offset-1 full-height" style="padding-top:4em">
	    <ul id="results_counter">
	      <li id="advisors_results_count" class="selected-result-type" ng-click="showResource('advisors')" ng-class="{'selected-result-type':display=='advisors','result-type':display!='advisors'}">
		<img src="/images/LittleAdvisorRed.png" width="25">
		Advisors
		<span>{{results.advisorsNumResults}}</span>
	      </li>
	      <li id="courses_results_count" class="result-type" ng-click="showResource('courses')" ng-class="{'selected-result-type':display=='courses','result-type':display!='courses'}">
		<img src="/images/LittleCourseRed.png" width="25">
		Courses
		<span>{{results.coursesNumResults}}</span>
	      </li>
	      <li id="theses_results_count" class="result-type" ng-click="showResource('theses')" ng-class="{'selected-result-type':display=='theses','result-type':display!='theses'}">
		<img src="/images/LittleThesisRed.png" width="25">
		Theses
		<span>{{results.thesesNumResults}}</span>
	      </li>
	      <li id="grants_results_count" class="result-type" ng-click="showResource('grants')" ng-class="{'selected-result-type':display=='grants','result-type':display!='grants'}">
		<img src="/images/LittleGrantRed.png" width="25">
		Funding
		<span>{{results.grantsNumResults}}</span>
	      </li>
	    </ul>
	    <table id="department_delims">
	      <tbody ng-if="display == 'advisors'">
		<tr>
		  <td colspan="3">
		    <h5>Faculty Funding</h5>
		  </td>
		</tr>
		<tr>
		  <td valign="top">
		    <input type="checkbox" value="hasFunding" ng-click="toggle()" check-list="delims.funding" id="funding_delim" />
		  </td>
		  <td valign="top" style="padding-left:0.35em">
		    <label for="funding_delim">Has Funding</label>
		  </td>
		  <td align="right" valign="middle">

		  </td>
		</tr>
	      </tbody>
	      <tbody ng-if="display == 'advisors'">
		<tr>
		  <td colspan="3">
		    <h5>Thesis</h5>
		  </td>
		</tr>
		<tr>
		  <td valign="top">
		    <input type="checkbox" value="hasThesis" ng-click="toggle()" check-list="delims.thesis" id="thesis_delim" />
		  </td>
		  <td valign="top" style="padding-left:0.35em">
		    <label for="thesis_delim">Advised a Thesis</label>
		  </td>
		  <td align="right" valign="middle">

		  </td>
		</tr>
	      </tbody>
	      <tbody>
		<tr>
		  <td colspan="3">
		    <h5 ng-if="testDrive == false"> Departments </h5>
		    <h5 ng-if="testDrive == true"> Universities </h5>
		  </td>
		</tr>
		<tr>
		  <td>
		    <input type="checkbox" id="select_all" ng-click="checkAll()" />
		  </td>
		  <td colspan="2" style="padding-left:0.35em">
		    <label for="select_all" id="select_all_label">{{selectAll}}</label>
		  </td>
		</tr>
		<tr ng-if="testDrive == true" ng-repeat="(key,university) in departments track by $index" ng-if="alphaExists(university);">
		  <td valign="top">
		    <input type="checkbox" value="{{university}}" ng-click="toggle(university)" check-list="delims.departments" name="department_names" id="delim_{{key}}" department="{{university}}" />
		  </td>
		  <td valign="top" style="padding-left:0.35em">
		    <label for="delim_{{key}}">{{university.replaceAll('_',' ')}}</label>
		  </td>
		  <td align="right" valign="middle">
		    {{resultsLength[display][university]}}
		  </td>
		</tr>
		<!-- SHOW DEPARTMENTS IF LOGGED IN -->
		<tr ng-if="testDrive == false" ng-repeat="(key,department) in departments track by $index" ng-if="alphaExists(university);">
		  <td valign="top">
		    <input type="checkbox" value="{{department}}" ng-click="toggle(department)" check-list="delims.departments" name="department_names" id="delim_{{key}}" department="{{department.replaceAll('_',' ')}}" />
		  </td>
		  <td valign="top" style="padding-left:0.35em">
		    <label for="delim_{{key}}">{{department}}</label>
		  </td>
		  <td align="right" valign="middle">
		    {{resultsLength[display][department]}}
		  </td>
		</tr>
	      </tbody>
	    </table>
	  </div>
	  <div class="col-xs-7 col-xs-offset-1 full-height">
	    <table class="match-parent" style="height:auto">
	      <tbody ng-repeat="(key,value) in results" id="{{key}}_results" ng-show="display == '{{key}}'">
		<tr>
		  <td>
		    <h3 style="text-transform: capitalize;"> We've matched you to {{getNumResults(key)}} {{key}} </h3>
		  </td>
		</tr>
		<?php
		include("results_table.html");
		?>
	      </tbody>
	    </table>
	    <!-- ALLOW PAGINATION USE IF LOGGED IN -->
	    <div id="pagination_buttons" ng-if="testDrive == false">
	      <span class="page-button" ng-show="resultsPage[display] > 6" style="font-weight:900;letter-spacing:3px" ng-click="changePage(resultsPage[display]-10>0?resultsPage[display]-10:5)">...</span>
	      <span ng-repeat="n in getPages()" class="page-button" ng-class="{'button-selected':n==resultsPage[display]}" ng-click="changePage(n)">
		{{n}}
	      </span>
	      <span class="page-button" ng-show="resultsPage[display]+6 < maxPages" style="font-weight:900;letter-spacing:3px" ng-click="changePage(resultsPage[display]+10<maxPages?resultsPage[display]+10:maxPages-5)">...</span>
	    </div>
	    <!-- RESTRICT PAGINATION USE IF NOT SIGNED UP -->
	    <div id="pagination_buttons" ng-if="testDrive == true">
	      <span class="page-button" ng-show="resultsPage[display] > 6" style="font-weight:900;letter-spacing:3px" onclick="showForm('reg_form')">...</span>
	      <span ng-repeat="n in getPages()" class="page-button" ng-class="{'button-selected':n==resultsPage[display]}" onclick="showForm('reg_form')">
		{{n}}
	      </span>
	      <span class="page-button" ng-show="resultsPage[display]+6 < maxPages" style="font-weight:900;letter-spacing:3px" onclick="showForm('reg_form')">...</span>
	    </div>
	  </div>
	</div>
      </div>
    </div>
  </div>
