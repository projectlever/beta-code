<?php 
/* Joobstrap Joomla Framework *index.php* v1.0.4  - 8.January 2013 - http://www.pixelsparadise.com */
/* Bootstrap v2.2.2  - 30th October 2012 - http://twitter.github.com/bootstrap/index.html */
/* Free to use under the MIT license.
/* http://www.opensource.org/licenses/mit-license.php*/

defined('_JEXEC') or die;
JLoader::import( 'joomla.version' );
$version = new JVersion();
$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$config = JFactory::getConfig();

// Check modules in left and right column besides the main content
$showHeader      = ($this->countModules('above_header_modules') or $this->countModules('header_modules') or $this->countModules('slider_modules') or $this->countModules('below_header_modules'));
$showLeft        = ($this->countModules('left_modules'));
$showRight       = ($this->countModules('right_modules'));

// Detecting Active Variables
$sitename = $app->getCfg('sitename');
?>

<!DOCTYPE html>
<html xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
  <script type="text/javascript">
  var analytics=analytics||[];(function(){var e=["identify","track","trackLink","trackForm","trackClick","trackSubmit","page","pageview","ab","alias","ready","group"],t=function(e){return function(){analytics.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var n=0;n<e.length;n++)analytics[e[n]]=t(e[n])})(),analytics.load=function(e){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src=("https:"===document.location.protocol?"https://":"http://")+"d2dq2ahtl5zl1z.cloudfront.net/analytics.js/v1/"+e+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n)};
  analytics.load("l0mz54o6kb");
</script>
  
<script type="text/javascript" src="http://static.dudamobile.com/DM_redirect.js"></script> <script type="text/javascript">DM_redirect("http://mobile.dudamobile.com/site/projectlever_3");</script>

<jdoc:include type="head" />
<link rel="shortcut icon" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/images/favicon.ico" />  
 <!-- Le styles -->
  <link href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/bootstrap.css" rel="stylesheet">
  <?php if($this->params->get('basicLayout') == 4 or $this->params->get('basicLayout') == 6) : ?>
    <link href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/bootstrap-responsive.css" rel="stylesheet">
    <?php endif; ?>
    <link href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/bootstrap_extend.css" rel="stylesheet">
    <link href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/flexslider.css" rel="stylesheet">
    <link href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/style.css" rel="stylesheet">
    <?php if($this->params->get('subTheme') != -1) : ?>
  
  <!-- Subthemes -->
  <link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/subthemes/<?php echo $this->params->get('subTheme'); ?>/style.css">
  <?php endif; ?>  
    
  <?php if($this->params->get('readDirection') == 0) : ?>
  <!-- Loads RTL stylsheet if enabled-->  
    <link rel="stylesheet" href="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/css/rtl.css" type="text/css" media="screen, projection" />
    <?php endif; ?>  
    
    <!-- Loads template specific extras -->
    <?php include ("include.php"); ?>
  
  <?php if($this->params->get('backgroundImagetype') == 1) : ?>  
  <!-- Loads background texture if enabled -->
    <style type="text/css">
      body {background-image: url(<?php echo $this->params->get('backgroundImage'); ?>);}
    </style>
  <?php endif; ?>  

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="http://twitter.github.com/bootstrap/assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-57-precomposed.png">
  </head>
<body onload="setUp()">

<?php if($this->params->get('basicLayout') == 5 or $this->params->get('basicLayout') == 6) : ?>
<?php if($this->countModules('sidebar_modules')) : ?>
<!-- ##################################################### SIDEBAR AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper sidebar_area_wrapper">
  <div class="inner_wrapper">
<!-- ************************ LOGO AREA START************************ -->  
    <div class="container-fluid">
              <div class="row-fluid">    
          <div class="logo_area">
        <?php if($this->params->get('logoType') == 1) : ?>
          <!-- Logo as image -->
      <a class="brand logo_image" href="<?php echo $this->baseurl;?>"><img src="<?php echo $this->baseurl;?><?php echo $this->params->get('logoFile'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>"  /></a>
    <?php endif; ?>
      
    <?php if($this->params->get('logoType') == 2) : ?>
      <!-- Logo as text taken from templates config-->
      <a class="brand logo_text" href="<?php echo $this->baseurl;?>"><h1><?php echo $this->params->get('logoText'); ?></h1></a>
    <?php endif; ?>
    
    <?php if($this->params->get('logoType') == 3) : ?>  
    <!-- Logo as moduleposition "logo" -->                      
        <a class="brand logo_module" href="<?php echo $this->baseurl;?>" ><jdoc:include type="modules" name="logo" style="raw" />  </a>                  
    <?php endif; ?>
    
    <?php if($this->params->get('logoType') == 4) : ?>
    <!-- Logo as image and text -->  
      <a class="brand logo_image_and_text" href="<?php echo $this->baseurl;?>"><img src="<?php echo $this->params->get('logoFile'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>"  /><h1><?php echo $this->params->get('logoText'); ?></h1></a>
    <?php endif; ?>
          </div>
        </div>
    </div>
    <?php if($this->countModules('main_nav')) : ?>
    <div class="container-fluid">
            <div class="row-fluid">
        <jdoc:include type="modules" name="main_nav" style="none" />
      </div>
        </div>
        <?php endif; ?>

<!-- ************************ LOGO AREA END************************ -->
    <div class="container-fluid">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="sidebar_modules" style="standard" />
      </div>
    </div>
    <div class="fixed_credits_area container-fluid">  
      <div class="row-fluid">
        Created and designed by <a href="http://www.pixelsparadise.com" target="_blank">Pixelsparadise.com</a>
      </div>
    </div>
  </div>
</div>
  
<!-- ##################################################### SIDEBAR AREA ENDS HERE ##################################################### -->
<?php endif; ?>
<?php endif; ?>

<div class="sourrounding_all <?php if($this->countModules('slider_modules')) : ?>header_active<?php endif; ?> <?php $menu = JSite::getMenu(); if ($menu->getActive() == $menu->getDefault()) { echo 'on_frontpage'; }?>
<?php $menu = JSite::getMenu(); if ($menu->getActive() != $menu->getDefault()) { echo 'not_on_frontpage'; }?>">
<?php if($this->countModules('above_all_modules')) : ?>
<!-- ##################################################### ABOVE AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper above_all_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="above_all_modules" style="standard" />
      </div>
    </div>
  </div>
</div>
  
<!-- ##################################################### ABOVE AREA ENDS HERE ##################################################### -->
<?php endif; ?>
<?php if($this->params->get('basicLayout') == 1 or $this->params->get('basicLayout') == 2 or $this->params->get('basicLayout') == 3 or $this->params->get('basicLayout') == 4) : ?>
<!-- ************************ LOGO AREA START************************ -->
<div class="sourrounding_wrapper logo_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">  
      <div class="navbar">
            <div class="navbar-inner">
              <div class="container-fluid">
                  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </a>
          
        <?php if($this->params->get('logoType') == 1) : ?>
          <!-- Logo as image -->
      <a class="brand logo_image" href="<?php echo $this->baseurl;?>"><img src="<?php echo $this->params->get('logoFile'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>"  /></a>
    <?php endif; ?>
      
    <?php if($this->params->get('logoType') == 2) : ?>
      <!-- Logo as text taken from templates config-->
      <a class="brand logo_text" href="<?php echo $this->baseurl;?>"><h1><?php echo $this->params->get('logoText'); ?></h1></a>
    <?php endif; ?>
    
    <?php if($this->params->get('logoType') == 3) : ?>  
    <!-- Logo as moduleposition "logo" -->                      
        <a class="brand logo_module" href="<?php echo $this->baseurl;?>" ><jdoc:include type="modules" name="logo" style="raw" />  </a>                  
    <?php endif; ?>
    
    <?php if($this->params->get('logoType') == 4) : ?>
    <!-- Logo as image and text -->  
      <a class="brand logo_image_and_text" href="<?php echo $this->baseurl;?>"><img src="<?php echo $this->params->get('logoFile'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>"  /><h1><?php echo $this->params->get('logoText'); ?></h1></a>
    <?php endif; ?>
    <?php if($this->countModules('main_nav')) : ?>
                  <div class="nav-collapse">
              <jdoc:include type="modules" name="main_nav" style="none" />
            </div>
            <?php endif; ?>
              </div>
            </div>
        </div>
    </div>
     </div>
</div>

<!-- ************************ LOGO AREA END************************ -->
<?php endif; ?>

<?php if ($showHeader) : ?>
<!-- ##################################################### HEADER AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper header_area_wrapper">
  <div class="inner_wrapper">

  <?php if($this->countModules('above_header_modules')) : ?>
<!-- ************************ ABOVE_HEADER MODULES START************************ -->
    <div class="container-fluid above_top_header_wrapper">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="above_header_modules" style="standard" />
      </div>
    </div>
<!-- ************************ ABOVE_HEADER MODULES END************************ -->
  <?php endif; ?>
  
  <?php if($this->countModules('header_modules')) : ?>
<!-- ************************ TOP HEADER START************************ -->
    <div class="container-fluid header_modules_wrapper">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="header_modules" style="standard" />
      </div>
    </div>
<!-- ************************ HEADER MODULES END************************ -->
  <?php endif; ?>

<!-- ************************ SLIDER MODULES START************************ -->
  <?php if($this->countModules('slider_modules')) : ?>
    <div class="container-fluid slider_modules_wrapper">  
      <div class="row-fluid slider_modules">
        <div class="slider_controls">
          <div class="flexslider">
              <ul class="slides">
                <jdoc:include type="modules" name="slider_modules" style="slider" />
              </ul>
            </div>
        </div>
      </div>
      </div>
  <?php endif; ?>  
<!-- ************************ SLIDER MODULES END************************ -->
  
  <?php if($this->countModules('below_header_modules')) : ?>
<!-- ************************ BELOW_HEADER MODULES START************************ -->
    <div class="container-fluid below_header_modules_wrapper">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="below_header_modules" style="standard" />
      </div>
    </div>
<!-- ************************ BELOW_HEADER MODULES END************************ -->
  <?php endif; ?>
  </div>
</div>
<!-- ##################################################### HEADER AREA ENDS HERE ##################################################### -->
<?php endif; ?>

<?php if($this->countModules('top_modules')) : ?>
<!-- ##################################################### TOP AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper top_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="top_modules" style="standard" />
      </div>
    </div>
  </div>
</div>
  
<!-- ##################################################### TOP AREA ENDS HERE ##################################################### -->
<?php endif; ?>

<?php if ($this->params->get('showComponent') or $showRight or $showLeft or $this->countModules('below_content_modules') or $this->countModules('above_content_modules')) : ?>
<!-- ************************ MAIN AREA START************************ -->
<div class="sourrounding_wrapper main_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">
      <div class="row-fluid main_area">
      
      <?php if($this->countModules('left_modules')) : ?>
      <!-- Left Modules Start-->
      <?php if ($this->params->get('showComponent') and $showRight and $showLeft or $showRight and $showLeft and $this->countModules('below_content_modules') or $this->countModules('above_content_modules')) : ?>
        <div class="span3 left_modules">
      <?php elseif ($showRight and $showLeft) : ?>
        <div class="span6 left_modules">
      <?php else: ?>
        <div class="span4 left_modules">
      <?php endif; ?>  
          <jdoc:include type="modules" name="left_modules" style="standard" />
        </div>
      <!-- Left Modules End-->
      <?php endif; ?>
      
      <?php if($this->countModules('above_content_modules') or $this->countModules('below_content_modules') or $this->params->get('showComponent')) : ?>
      <!-- Main Content Area Start -->
        <div class="<?php if ($showRight and $showLeft) : ?>span6<?php else: ?><?php if ($showRight or $showLeft) : ?>span8<?php else: ?>span12<?php endif; ?><?php endif; ?> main_content">
        
        <?php if($this->countModules('above_content_modules')) : ?>
        <!-- Above Content Modules Start -->
        <div class="row-fluid">
          <jdoc:include type="modules" name="above_content_modules" style="standard" />
        </div>
        <!-- Above Content Modules End -->
        <?php endif; ?>
        
        <?php if($this->params->get('showComponent')) : ?>
        <!-- Main Component Area Start -->
        <jdoc:include type="message" />
        <jdoc:include type="component" />
        <!-- Main Component Area End -->
        <?php endif; ?>
        
        <?php if($this->countModules('below_content_modules')) : ?>
        <!-- Below Content Modules Start -->
        <div class="row-fluid">
          <jdoc:include type="modules" name="below_content_modules" style="standard" />
        </div>
        <!-- Below Content Modules End -->
        <?php endif; ?>
        
        </div>
      <!-- Main Content Area End -->
      <?php endif; ?>
      
      <?php if($this->countModules('right_modules')) : ?>
      <!-- Right Modules Start-->
      <?php if ($this->params->get('showComponent') and $showRight and $showLeft or $showRight and $showLeft and $this->countModules('below_content_modules') or $this->countModules('above_content_modules')) : ?>
        <div class="span3 right_modules">
      <?php elseif ($showRight and $showLeft) : ?>
        <div class="span6 right_modules">
      <?php else: ?>
        <div class="span4 right_modules">
      <?php endif; ?>  
          <jdoc:include type="modules" name="right_modules" style="standard" />
        </div>
      <!-- Right Modules End-->
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- ************************ MAIN AREA END************************ -->  
<?php endif; ?>

<?php if($this->countModules('bottom_modules')) : ?>
<!-- ##################################################### BOTTOM AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper bottom_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="bottom_modules" style="standard" />
      </div>
    </div>
  </div>
</div>
  
<!-- ##################################################### BOTTOM AREA ENDS HERE ##################################################### -->
<?php endif; ?>

<?php if($this->countModules('footer_modules')) : ?>
<!-- ##################################################### FOOTER AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper footer_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="footer_modules" style="standard" />
      </div>
    </div>
  </div>
</div>
  
<!-- ##################################################### FOOTER AREA ENDS HERE ##################################################### -->
<?php endif; ?>

<?php if($this->countModules('subfooter_modules')) : ?>
<!-- ##################################################### SUBFOOTER AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper subfooter_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">  
      <div class="row-fluid">
        <jdoc:include type="modules" name="subfooter_modules" style="standard" />
      </div>
    </div>
  </div>
</div>
<!-- ##################################################### SUBFOOTER AREA ENDS HERE ##################################################### -->
<?php endif; ?>

<?php if($this->params->get('basicLayout') != 5 and $this->params->get('basicLayout') != 6) : ?>
<!-- ##################################################### CREDITS AREA STARTS HERE ##################################################### -->
<div class="sourrounding_wrapper credits_area_wrapper">
  <div class="inner_wrapper">
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span6 credits_left">
          <p>Created and designed by <a href="http://www.pixelsparadise.com" target="_blank">Pixelsparadise.com</a><br />With help of <a href="http://twitter.github.com/bootstrap/">Bootstrap Framework</a> and Flexslider by <a href="http://flex.madebymufffin.com">Madebymufffin</a></p>
          <p class="copyright_by">&copy;  <?php echo date('Y');?> by <a href="<?php echo $this->baseurl;?>"><?php echo $app->getCfg('sitename'); ?></a></p>
        </div>
      
        <div class="span6 credits_right">
        <p class="text-align-right"><a href="http://www.joomla.org" title="Click here to visit Joomla!" target="_blank">Joomla! ®</a> name is used under a limited license from <a href="http://opensourcematters.org" title="Click here to visit Open Source Matters" target="_blank">Open Source Matters</a><br>
        <a title="pixelsparadise.com" href="http://www.pixelsparadise.com">Pixelsparadise.com</a> is not affiliated with the <a title="Joomla! Home" href="http://www.joomla.org">Joomla!</a> Project.</p>
        <p class="back_to_the_top text-align-right"><a href="#top" id="back-top"><i class="icon-circle-arrow-up icon-white"></i> Back to top</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- ##################################################### CREDITS AREA ENDS HERE ##################################################### -->
<?php endif; ?>
</div>
<!-- Checks if Joomla 2.5 or lower or 3.0 or higher is in use and if the jQuery frameworks was already loaded to avoid incompatibility issues -->
<?php
if (version_compare( $version->RELEASE, '2.5', '<=')) {
if(JFactory::getApplication()->get('jquery') !== true) {
        // load jQuery here
        JFactory::getApplication()->set('jquery', true);
        // add jQuery
  JFactory::getDocument()->addScript("http://code.jquery.com/jquery-latest.pack.js");
    }
      if($this->params->get('mootools') == 1) {
         JHTML::_('behavior.mootools');
         }
        JFactory::getDocument()->addScript("/templates/goodkarma/js/bootstrap.min.js");
        // load jQuery, if not loaded before
} else {
JHtml::_('jquery.framework');
JHtml::_('bootstrap.framework');
if($this->params->get('mootools') == 1) {
         JHTML::_('behavior.framework');
         }
}
?>
<script src="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/js/jquery.flexslider-min.js"></script>
<script type="text/javascript">jQuery.noConflict();</script>
<?php if($this->countModules('slider_modules')) : ?>

<!--Hook up the FlexSlider-->
<script type="text/javascript">
  jQuery.noConflict();
    jQuery(window).load(function() {
        jQuery('.flexslider').flexslider({
            <?php if($this->params->get('sliderEffect') == 1) : ?>animation: "fade",<?php endif; ?>
            <?php if($this->params->get('sliderEffect') == 2) : ?>animation: "slide",<?php endif; ?>
            slideDirection: "horizontal",
            <?php if($this->params->get('sliderAuto') == 1) : ?>slideshow: true, <?php endif; ?>
            <?php if($this->params->get('sliderAuto') == 0) : ?>slideshow: false, <?php endif; ?>
            slideshowSpeed: <?php echo $this->params->get('sliderTime'); ?>,
            animationDuration: <?php echo $this->params->get('sliderChange'); ?>,
            <?php if($this->params->get('sliderArrows') == 0) : ?>directionNav: false,<?php endif; ?>
            <?php if($this->params->get('sliderArrows') == 1) : ?>directionNav: true,<?php endif; ?>
            <?php if($this->params->get('sliderPagination') == 0) : ?>controlNav: false,<?php endif; ?>
            <?php if($this->params->get('sliderPagination') == 1) : ?>controlNav: true,<?php endif; ?>
            <?php if($this->params->get('loopSlider') == 0) : ?>animationLoop: false, <?php endif; ?>
            <?php if($this->params->get('loopSlider') == 1) : ?>animationLoop: true, <?php endif; ?>
            pauseOnAction: true, 
            smoothHeight: true,
            <?php if($this->params->get('hoverPause') == 0) : ?>pauseOnHover: false,<?php endif; ?> 
            <?php if($this->params->get('hoverPause') == 1) : ?>pauseOnHover: true,<?php endif; ?>         
          });
      });
</script>    
<?php endif; ?>   

<?php if($this->params->get('backgroundImagetype') == 2) : ?>
<!-- Background Image Settings -->
<script type="text/javascript" src="<?php echo $this->baseurl;?>/templates/<?php echo $this->template;?>/js/jquery.backstretch.min.js"></script>
    <script type="text/javascript">
  
     jQuery.backstretch([
          "<?php echo $this->baseurl;?>/<?php echo $this->params->get('backgroundImage'); ?>"
        ], {
            fade: 750
        });

</script>
<?php endif; ?>    
</body>
</html>