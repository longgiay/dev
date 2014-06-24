<?php
if (eregi(basename( __FILE__ ), $_SERVER['PHP_SELF'])) {
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}

class Pager{
	var $lang = array(	
					"tpl_gotolast" => "Tới trang cuối",
					"tpl_gotofirst" => "Về trang đầu",
					"tpl_next" => "Trang tiếp",
					"tpl_previous" => "Trang trước",
					"tpl_jump" => "Chuyển nhanh",
					"tpl_pages" => "Trang",
					  );
	var $limit = 50;
	var $offset = 0;
	var $page = 1;
	var $total = 0;
	var $link = "";
	var $limit_page_num = 200; // Unlimit if $limit_page_num = "~";
	var $page_param = "page";
	var $type = null;
	var $catid = 0;
	var $tt = 0;
	
    function query_str ($params) {
        $str = '';
        foreach ($params as $key => $value) {
            $str .= (strlen($str) < 1) ? '' : '&';
            $str .= $key . '=' . $value;
        }
        return ($str);
    }	
   
	function proper_parse_str($exclude_array = array(), $str = "") {
	
	  # result array
	  $arr = array();
	  //$exclude_array  = array('p');
	  $uri = parse_url($_SERVER['REQUEST_URI']);
	  
	  if(!$str && isset($uri['query'])){
		$str = $uri['query'];
	  }
	  $str = ($str) ? $str : $_SERVER['QUERY_STRING'];

	  # split on outer delimiter
	  $pairs = explode('&', $str);
	
	  # loop through each pair
	  foreach ($pairs as $i) {
	    # split into name and value
	    list($name,$value) = explode('=', $i, 2);
		   if(!in_array($name, $exclude_array)){
			    # if name already exists
			    if( isset($arr[$name]) ) {
			      # stick multiple values into an array
			      if( is_array($arr[$name]) ) {
			        $arr[$name][] = $value;
			      }
			      else {
			        $arr[$name] = array($arr[$name], $value);
			      }
			    }
			    # otherwise, simply stick it in a scalar
			    else {
			      $arr[$name] = $value;
			    }
		   }
	  }
	  
	  //$query = http_build_query($arr);
	  $query = $this->query_str($arr);
	  // return url 
	  $url = '';
	  if( $uri['path'] && $uri['query'] )
	  {
	  	$url .=  $uri['path'];
	  }
	
	  $url .= $query ? '?'.$query : ''; 
	  
	  return $url;		
	  # return result array
	 // return $arr;
	}

	/**
	 
	* Will remove the passed in string from the url query string
	* NOTE: This changes a global! If you do not desire this
	*  sort of functionality change the last line to a return rather
	*  than an assignment.
	*
	* @param string $needle - String to look for and remove
	*/
	function remove_from_query_string($content, $needle) {
	    $query_string = $_SERVER['QUERY_STRING']; // Work on a seperate copy and preserve the original for now
	   $query_string = preg_replace("/\&$needle=[a-zA-Z0-9].*?(\&|$)/", '&',   $query_string);
	   $query_string = preg_replace("/(&)+/","&",$query_string); // Supposed to pull out all repeating &amp;s, however it allows 2 in a row(&&). Good enough for now
	   $_SERVER['QUERY_STRING']  = $query_string;
	}


    function get_offset1()	
    {
    	return $this->offset;
    }
    
    function limit_from()	
    {
        $limit    = max((int) $this->limit, 1);
        $page     = (int) $this->page;
        $page= ($page== "") ? 1 : $page;	
        
		$limit_from = (($page - 1) * $limit) + 1;        
    	return $limit_from;
    }
    
    function limit_to()	
    {
        $limit    = max((int) $this->limit, 1);
        $page     = (int) $this->page;
        $page= ($page== "") ? 1 : $page;	
        
		$limit_to = ($page * $limit);        
    	return $limit_to;
    }
    
    function get_offset()	
    {
        $limit    = max((int) $this->limit, 1);
        $page     = (int) $this->page;
        $page = ($page== "") ? 1 : $page;	
        
		$this->offset = ($page - 1) * $limit;        
    	return $this->offset;
    }
            
        
	function page_link()
	{
        $total  = (int) $this->total;
        $limit    = max((int) $this->limit, 1);
        $page     = (int) $this->page;
		$page = ($page== "") ? 1 : $page;	
				
		if ( $total > 0 )
		{		
			$total = ceil($total/$limit);
		}

		//Limit page number
		
		$total = ($this->limit_page_num == "~") ? $total : ((int)($total > $this->limit_page_num) ? $this->limit_page_num : $total);
			
		$this->offset = ($page - 1) * $limit;
		
		
		if ($total <= 1) {
			return "";
		}
		$v_f = 3;
		$v_a = 2;
		$v_l = 3;
		$max_pages = $v_f + $v_a + $v_l + 5;
		$z_1 = $z_2 = $z_3 = false;
		$link = "";
		$link .= ($this->link=="") ? $this->proper_parse_str(array($this->page_param)) : $this->link;
		//die($link);
		//$link .= strpos($link, '?') ? '&' : '?'; 
		$link .= (substr($link, -1) == '?') ? '' : '&';
		
		//echo $link;
		//die(basename($_SERVER['PHP_SELF']));
		
		$pg = $this->page ? $this->page : 1;		
		$work['B_LINK'] = "";
		$work['F_LINK'] = "";
		$work['P_LINK'] = "";
		$work['N_LINK'] = "";
		$work['L_LINK'] = "";
		
		$pgt = $pg-1;
		if ($pg != 1)
		{
			$work['F_LINK']	=	$this->pagination_start_dots(1);
			$work['P_LINK']	=	$this->pagination_previous_link($pgt);
			//$work['F_LINK']	=	$this->pagination_start_dots("{$link}{$this->page_param}=1");
			//$work['P_LINK']	=	$this->pagination_previous_link("{$link}{$this->page_param}=$pgt");
			
		}
		for($m = 1; $m <= $total; $m++) {
			if ($total > $max_pages) {
				if (($m > $v_f) && (($m < $pg - $v_a) || ($m > $pg + $v_a)) && ($m < $total - $v_l + 1)) {
					if (!$z_1 && ($m > $v_f)) {
						$work['B_LINK'] .= "...";
						$z_1 = true;
					}
					elseif (!$z_2 && ($m > $pg + $v_a)) {
						$work['B_LINK'] .= "...";
						$z_2 = true;
					}
					continue;
				}
			}	
			if($m == $pg)
			{
				$work['B_LINK'] .= $this->pagination_current_page($m);
			}
			else
			{
				$work['B_LINK'] .= $this->pagination_page_link("{$link}{$this->page_param}={$m}", $m);		
			}
		}	
		$pgs = $pg + 1;
		if ($pg != $total)
		{
			//$work['N_LINK']	=	$this->pagination_next_link("{$link}{$this->page_param}=$pgs");
			//$work['L_LINK']	=	$this->pagination_end_dots("{$link}{$this->page_param}=$total");
			$work['N_LINK']	=	$this->pagination_next_link($pgs);
			$work['L_LINK']	=	$this->pagination_end_dots($total);
		}
		$html = $work['F_LINK'].$work['P_LINK'].$work['B_LINK'].$work['N_LINK'].$work['L_LINK'];
		return $html;
	}
	
	function build_pagelinks($data)
	{
	
			$work = array();
			
			$section = ($data['leave_out'] == "") ? 3 : $data['leave_out'];  // Number of pages to show per section( either side of current), IE: 1 ... 4 5 [6] 7 8 ... 10
			
			$use_st  = ($data['USE_ST'] == "") ? 'page' : $data['USE_ST'];
			
			$data['PER_PAGE'] = ($data['PER_PAGE']== "") ? $this->limit : $data['PER_PAGE'];
			
			//-----------------------------------------
			// Get the number of pages
			//-----------------------------------------
			
			if ( $data['TOTAL_POSS'] > 0 )
			{
				$work['pages'] = ceil( $data['TOTAL_POSS'] / $data['PER_PAGE'] );
			}
			
			$work['pages'] = $work['pages'] ? $work['pages'] : 1;
			
			//-----------------------------------------
			// Set up
			//-----------------------------------------
			
			$work['total_page']   = $work['pages'];
			$work['current_page'] = $data['CUR_ST_VAL'] > 0 ? ($data['CUR_ST_VAL'] / $data['PER_PAGE']) + 1 : 1;
			
			//-----------------------------------------
			// Next / Previous page linkie poos
			//-----------------------------------------
			
			$previous_link = "";
			$next_link     = "";
			
			if ( $work['current_page'] > 1 )
			{
				$start = $data['CUR_ST_VAL'] - $data['PER_PAGE'];
				$previous_link = $this->pagination_previous_link("{$data['BASE_URL']}&amp;$use_st=$start");
			}
			
			if ( $work['current_page'] < $work['pages'] )
			{
				$start = $data['CUR_ST_VAL'] + $data['PER_PAGE'];
				$next_link = $this->pagination_next_link("{$data['BASE_URL']}&amp;$use_st=$start");
			}
			
			//-----------------------------------------
			// Loppy loo
			//-----------------------------------------
			
			if ($work['pages'] > 1)
			{
				$work['first_page'] = $this->pagination_make_jump($data['TOTAL_POSS'],$data['PER_PAGE'], $data['BASE_URL'], $work['pages']);
				
				for( $i = 0; $i <= $work['pages'] - 1; ++$i )
				{
					$RealNo = $i * $data['PER_PAGE'];
					$PageNo = $i+1;
					
					if ($RealNo == $data['CUR_ST_VAL'])
					{
						$work['page_span'] .=  $this->pagination_current_page($PageNo);
					}
					else
					{
						if ($PageNo < ($work['current_page'] - $section))
						{
							$work['st_dots'] = $this->pagination_start_dots($data['BASE_URL']);
							continue;
						}
						
						// If the next page is out of our section range, add some dotty dots!
						
						if ($PageNo > ($work['current_page'] + $section))
						{
							$work['end_dots'] = $this->pagination_end_dots("{$data['BASE_URL']}&amp;$use_st=".($work['pages']-1) * $data['PER_PAGE']);
							break;
						}
						
						
						$work['page_span'] .= $this->pagination_page_link("{$data['BASE_URL']}&amp;$use_st={$RealNo}",$PageNo);
					}
				}
				
				$work['return']    = $this->pagination_compile($work['first_page'],$previous_link,$work['st_dots'],$work['page_span'],$work['end_dots'],$next_link);
			}
			else
			{
				//$work['return']    = $data['L_SINGLE'];
			}
		
			return $work['return'];
	}

//===========================================================================
// pagination_current_page
//===========================================================================
function pagination_current_page($page="") {

$IPBHTML = "";
//--starthtml--//


$IPBHTML .= <<<EOF
&nbsp;<span class="pagecurrent">{$page}</span>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// pagination_end_dots
//===========================================================================
function pagination_end_dots($url="") {

$IPBHTML = "";
//--starthtml--//
	
	if ($this->type=='search'){
	//$url = $this->convert_url($url);
	$IPBHTML .= <<<EOF
	&nbsp;<a href="javascript:void(0)" onclick="javascript:search_cat($this->catid,$this->total,$url,$this->tt);window.scrollTo(0,0);" title="{$this->lang['tpl_gotolast']}" class="pagelinklast">&raquo;&raquo;</a>&nbsp;
EOF;
}
else{

	$IPBHTML .= <<<EOF
	&nbsp;<a href="$url" title="{$this->lang['tpl_gotolast']}" class="pagelinklast">&raquo;&raquo;</a>&nbsp;
EOF;
}


//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// pagination_make_jump
//===========================================================================
function pagination_make_jump($tp="",$pp="",$ub="",$pages=1) {

$IPBHTML = "";
//--starthtml--//


$IPBHTML .= <<<EOF
<a title="{$this->lang['tpl_jump']}" href="javascript:multi_page_jump('$ub',$tp,$pp,$this->limit);" class="pagelink">$pages {$this->lang['tpl_pages']}</a>&nbsp;
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// pagination_next_link
//===========================================================================
function pagination_next_link($url="") {

$IPBHTML = "";
//--starthtml--//

if ($this->type=='search'){
	$IPBHTML .= <<<EOF
	&nbsp;<a href="javascript:void(0)" onclick="javascript:search_cat($this->catid,$this->total,$url,$this->tt);window.scrollTo(0,0);" title="{$this->lang['tpl_next']}" class="pagelink">&gt;</a>
EOF;
}
else{
	$IPBHTML .= <<<EOF
	&nbsp;<a href="$url" title="{$this->lang['tpl_next']}" class="pagelink">&gt;</a>
EOF;
}


//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// pagination_page_link
//===========================================================================
function pagination_page_link($url="",$page="") {

$IPBHTML = "";
//--starthtml--//

if ($this->type=='search'){
	$IPBHTML .= <<<EOF
	&nbsp;<a href="javascript:void(0)" onclick="javascript:search_cat($this->catid,$this->total,$page,$this->tt);window.scrollTo(0,0);" title="$page" class="pagelink">$page</a>
EOF;
}
else {

	$IPBHTML .= <<<EOF
	&nbsp;<a href="$url" title="$page" class="pagelink">$page</a>
EOF;
}

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// pagination_previous_link
//===========================================================================
function pagination_previous_link($url="") {

$IPBHTML = "";
//--starthtml--//

if ($this->type=='search'){
	//$url = $this->convert_url($url);
	$IPBHTML .= <<<EOF
<a href="javascript:void(0)" onclick="javascript:search_cat($this->catid,$this->total,$url,$this->tt);window.scrollTo(0,0);" title="{$this->lang['tpl_previous']}"  class="pagelink">&lt;</a>
EOF;
}
else{

	$IPBHTML .= <<<EOF
<a href="$url" title="{$this->lang['tpl_previous']}"  class="pagelink">&lt;</a>
EOF;
}
//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// pagination_start_dots
//===========================================================================
function pagination_start_dots($url="") {

$IPBHTML = "";
//--starthtml--//

if ($this->type=='search'){
	//$url = $this->convert_url($url);
	$IPBHTML .= <<<EOF
<a href="javascript:void(0)" onclick="javascript:search_cat($this->catid,$this->total,1,$this->tt);window.scrollTo(0,0);" title="{$this->lang['tpl_gotofirst']}" class="pagelinklast">&laquo;&laquo;</a>&nbsp;
EOF;
}
else{
	$IPBHTML .= <<<EOF
<a href="$url" title="{$this->lang['tpl_gotofirst']}" class="pagelinklast">&laquo;&laquo;</a>&nbsp;
EOF;

}
//--endhtml--//
return $IPBHTML;
}	

//===========================================================================
// pagination_compile
//===========================================================================
function pagination_compile($start="",$previous_link="",$start_dots="",$pages="",$end_dots="",$next_link="") {

$IPBHTML = "";
//--starthtml--//


$IPBHTML .= <<<EOF
{$start}{$start_dots}{$previous_link}{$pages}{$next_link}{$end_dots}
EOF;

//--endhtml--//
return $IPBHTML;
}



}
?>