var analytics=analytics||[];analytics.load=function(e){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src=("https:"===document.location.protocol?"https://":"http://")+"d2dq2ahtl5zl1z.cloudfront.net/analytics.js/v1/"+e+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n);var r=function(e){return function(){analytics.push([e].concat(Array.prototype.slice.call(arguments,0)))}},i=["identify","track","trackLink","trackForm","trackClick","trackSubmit","pageview","ab","alias","ready","group"];for(var s=0;s<i.length;s++)analytics[i[s]]=r(i[s])};
analytics.load("l0mz54o6kb");
window.addEvent('load', function() {
	new JCaption('img.caption');
});
$(document).ready(function(){
	$(".emailDiv").mouseover(function(){
		$(this).css({
			"background-color":"#ccc",
			"color":"#800"
		});
	});
	$(".emailDiv").on("mouseexit mouseleave",function(){
		$(this).css({
			"background-color":"#eee",
			"color":"#555"
		});
	});
	$(".X").click(function(){
		$(".emailBox").hide();
	});
	$("h1").each(function(){
	    var elem = $(this);
	    if ( elem.html() == "" ){
	        elem.remove();
	    }
	});
	$("#about_block [style*='background-color']").css("background-color","transparent");
});

function contact(e){
	$(".emailBox").slideDown("fast");
}

function saveProfessor(){
    var professorId = $("#professor_id").val();
    var type        = $("#type").val();
    $.post("./save2.php",{"id":professorId,"type":type,"saved":$("#save_remove_button").html()},function(data){
        // This function returns the HTML the button is supposed to be changed to
        $("#save_remove_button").html(data).removeAttr("disabled");
    });
    $("#save_remove_button").html("Loading...").prop("disabled","disabled");
    return;
}
function d3load(){
	
	function name(d) { return d.name; }
	function group(d) { return d.group; }

	var color = d3.scale.category10();
	function colorByGroup(d) { return color(group(d)); }

	var width = 600,
	height = 400;

	var svg = d3.select('#viz')
		.append('svg')
		.attr('width', width)
		.attr('height', height);
console.log(svg);
	var node, link;

	var voronoi = d3.geom.voronoi()
		.x(function(d) { return d.x; })
		.y(function(d) { return d.y; })
		.clipExtent([[-10, -10], [width+10, height+10]]);

	function recenterVoronoi(nodes) {
	var shapes = [];
	voronoi(nodes).forEach(function(d) {
		if ( !d.length ) return;
			var n = [];
			d.forEach(function(c){
				n.push([ c[0] - d.point.x, c[1] - d.point.y ]);
			});
			n.point = d.point;
			shapes.push(n);
		});
		return shapes;
	}


	// Define variable that stores force; function returns new position of node and links
	var forcePosition = d3.layout.force()
		.charge(-4000)
		.friction(0.2)
		.gravity(0.4)
		//.linkDistance(200)
		.size([width, height]);

	forcePosition.on('tick', function() {
		node.attr('transform', function(d) { return 'translate('+d.x+','+d.y+')'; })

		node.select('circle').attr('clip-path', function(d) { return 'url(#clip-'+d.index+')'; });

		link.attr('x1', function(d) { return d.source.x; })
			.attr('y1', function(d) { return d.source.y; })
			.attr('x2', function(d) { return d.target.x; })
			.attr('y2', function(d) { return d.target.y; });
	
		//update clip path
		var clip = svg.selectAll('.clip')
			.data( recenterVoronoi(node.data()), function(d) { return d.point.index; } );

		clip.enter().append('clipPath')
			.attr('id', function(d) { return 'clip-'+d.point.index; })
			.attr('class', 'clip');
		clip.exit().remove()

		clip.selectAll('path').remove();
		clip.append('path')
			.attr('d', function(d) { return 'M'+d.join(',')+'Z'; });
	});

	// Load data here from json and initialize viz
	d3.json('http://projectlever.com/advisor_viz/'+id+'.json', function(err, data) {
		
		for(var i = 0, n = data.Nodes.length; i < n; i++){
			if(weights[data.Nodes[i].name])
				data.Nodes[i].group = 1;
		}
		
		data.Nodes.forEach(function(d, i) {
			d.id = i;
		});

		link = svg.selectAll('.link')
			.data( data.Links )
			.enter().append('line')
			.attr('class', 'link')
			.style("stroke-width", function(d) { return Math.sqrt(d.value); });

		node = svg.selectAll('.node')
			.data( data.Nodes )
			.enter().append('g')
			.attr('title', name)
			.attr('class', 'node')
			.call( forcePosition.drag );

		node.append('circle')
			.attr('r', function(d) {return d.size*120 })
			.attr('fill', colorByGroup)
			.attr('fill-opacity', 0.5)
			.attr('clip-path', function(d) { return 'url(#clip-'+this.parentNode.index+')'; });

		// code for making black circle at center of node
		/*node.append('circle')
			.attr('r', 4)
			.attr('stroke', 'black');*/
  
		// code for making text at center of node
		node.append('text')
			.text(function(d) { return d.name })
			.attr("text-anchor", "middle")

		// when hover over node, enlarge text; restore text size when mouseout
		node.on("mouseover", function(d){
			d3.select(this).select('text').attr("font-size", "36px");
		})
		node.on("mouseout", function(d){
			d3.select(this).select('text').attr("font-size", "14px");
		})

		// when click on node, run node_onclick() fxn; also stop propagation to higher DOM elements

		node.on("click", function(d){
			d3.event.stopPropagation(); //stop propagation to higher DOM elements to prevent click to svg
			if (d3.event.defaultPrevented) return; // ignore click when dragging
			node_onClick(d); // otherwise run node_onClick function
		})

		// start forcePosition here
		forcePosition
			.nodes( data.Nodes )
			.links( data.Links )
			.linkDistance(function(d) { return 150/d.value; })
			.start();

		});

		// select toolTip
		var studbox = d3.select(document.getElementById("studbox"));

		// define node_onClick fxn; want it to display toolTip on click
		function node_onClick(d) {
			// add students
			var stu = [];
			d.students.forEach(function(c){
				stu.push('<tr><td><a href=\'' + c.Link + '\'>' + c.Name + '</a></td></tr>');
			});
			if(stu.length){
				studbox.select('table').html(stu.join('\n'));
			}
			else {
				studbox.select('table').html('No students available.');
			}

			// set display to inline
			studbox.transition()
					.duration(200)
					.style("display","inline");	
	
			// define starting topleft position on toolTip
			studbox.style("left", d3.event.pageX + "px")
					.style("top", d3.event.pageY + "px");
		}

		// set toolTip to none when click outside of Tooltip

		d3.select('svg').on("click",function() {
		studbox.transition()		    						
			.duration(500)
			.style("display","none");
	});

	studbox.on("click",function(){
		d3.event.stopPropagation();
	});
}
