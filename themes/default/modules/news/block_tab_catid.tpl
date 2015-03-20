<!-- BEGIN: main -->
<!-- BEGIN: load_script_tab -->
<link rel="stylesheet" type="text/css" media="screen" href="{NV_BASE_SITEURL}themes/{TEMPLATE}/css/tab.css" />
<script type="text/javascript" src="{NV_BASE_SITEURL}themes/{TEMPLATE}/js/tab.min.js"></script>
<!-- END: load_script_tab -->
<ul class="boxtab">
    <!-- BEGIN: loopcat -->
	<li id="tab{TAB_NAME}{NUM}"><span onclick="changeTab('tab{TAB_NAME}','{NUM}','{TAB_TOTAL}');">{CAT.title}</span></li>
	<!-- END: loopcat -->
    
</ul>
<div class="boxtabcontent">  
	<!-- BEGIN: loop -->
	<div class="tabcontent" id="tab{TAB_NAME}{NUM}Content" style="{LOOP.style}">
        <!-- BEGIN: loopcontent -->
		<div class="itemblock">
            <a class="item" href="{LOOP.link}"><img class="itemimg" src="{LOOP.thumb}" alt="{LOOP.title}" />{LOOP.title_cut}</a>
        </div>
		<!-- END: loopcontent -->
    </div>
	 <!-- END: loop --> 
</div>
<div class="clear"></div>
<script type="text/javascript">
    changeTab('tab{TAB_NAME}', '0', '{TAB_TOTAL}');
</script>
<!-- END: main -->