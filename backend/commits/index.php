<!DOCTYPE html>
<head>
  <!-- test -->
  <title>Commits</title>
  <script type="text/javascript" src="/web-lib/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="/web-lib/angular.min.js"></script>
  <script type="text/javascript" src="/web-lib/handlebars-v1.3.0.js"></script>
  <script type="text/javascript" src="diff.js"></script>
  <link rel="stylesheet" type="text/css" href="main.css">
</head>
<body ng-app="diffTool" ng-controller="diffToolListCtrl as diff" 
      ng-init="diff.curCommit=0;diff.prevCommit=1">
  <ul id="commits" ng-click="diff.detail()">
    <li ng-repeat="commit in diff.commits">
      <table>
	<tbody>
	  <tr>
	    <td>
	      {{commit.author}}<br/>
	      {{commit.date}}<br/>
	      {{commit.time}}<br/>
	    </td>
	    <td>
	      <span id="commit_id">
		{{commit.commit_id}}
	      </span>
	      <br/>
	      {{commit.commit_msg}}
	    </td>
	  </tr>
	</tbody>
      </table>
    </li>
  </ul>
  <div id="commit_files">
    <table>
      <tr>
	<td>
	  <ul id="files" ng-repeat="file in diff.files">
	    <li ng-click="diff.commitDetails()">
	      {{file}}
	    </li>
	  </ul>
	</td>
	<td>
	  <div id="controls">
	    <table>
	      <tbody>
		<tr>
		  <td>
		    Compare commit:
		  </td>
		  <td>
		    <input type="number" ng-model="diff.curCommit"
			   required ng-change="diff.commitDetails()">
		  </td>
		</tr>
		<tr>
		  <td>
		    to commit: 
		  </td>
		  <td>
		    <input type="number" ng-model="diff.prevCommit" 
			   required ng-change="diff.commitDetails()">
		  </td>
		</tr>
	      </tbody>
	    </table>
	    <b>NOTE:</b><br/>
	    Commit 0 = Current Commit<br/>
	    Commit 1 = Previous Commit<br/>
	    Commit 2 = 2 commits back<br/>
	    Commit n = n commits back<br/>
	  </div>
	  <div id="file_diff" ng-model="diff.fileDiff">
	    {{diff.fileDiff}}
	  </div>
	</td>
      </tr>
    </table>
    <span onclick="$('#commit_files').hide()" id="close_button">X</span>
  </div>
</body>
</html>
