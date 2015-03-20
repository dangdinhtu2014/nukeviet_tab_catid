<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sat, 10 Dec 2011 06:46:54 GMT
 */

if( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

if( ! nv_function_exists( 'getParentSub' ) )
{
	function getParentSub( $catid )
	{
		global $module_array_cat;
		$array_cat = array();
		$array_cat[] = $catid;
		$subcatid = explode( ',', $module_array_cat[$catid]['subcatid'] );
		if( ! empty( $subcatid ) )
		{
			foreach( $subcatid as $id )
			{
				if( $id > 0 )
				{
					if( $module_array_cat[$id]['numsubcat'] == 0 )
					{
						$array_cat[] = $id;
					}
					else
					{
						$array_cat_temp = getParentSub( $id );
						foreach( $array_cat_temp as $catid_i )
						{
							$array_cat[] = $catid_i;
						}
					}
				}
			}
		}
		return array_unique( $array_cat );
	}
}

if( ! nv_function_exists( 'creat_thumbs' ) )
{
	function creat_thumbs( $id, $homeimgfile, $module_name, $width = 200, $height = 150 )
	{
		if( $width >= $height ) $rate = $width / $height;
		else  $rate = $height / $width;

		$image = NV_UPLOADS_REAL_DIR . '/' . $module_name . '/' . $homeimgfile;

		if( $homeimgfile != '' and file_exists( $image ) )
		{
			$imgsource = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/' . $homeimgfile;
			$imginfo = nv_is_image( $image );
			$basename = $module_name . $width . 'x' . $height . '-' . $id . '-' . md5_file( $image ) . '.' . $imginfo['ext'];

			if( file_exists( NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . $basename ) )
			{
				$imgsource = NV_BASE_SITEURL . NV_TEMP_DIR . '/' . $basename;
			}
			else
			{
				require_once NV_ROOTDIR . '/includes/class/image.class.php';

				$_image = new image( $image, NV_MAX_WIDTH, NV_MAX_HEIGHT );

				if( $imginfo['width'] <= $imginfo['height'] )
				{
					$_image->resizeXY( $width, 0 );

				}
				elseif( ( $imginfo['width'] / $imginfo['height'] ) < $rate )
				{
					$_image->resizeXY( $width, 0 );
				}
				elseif( ( $imginfo['width'] / $imginfo['height'] ) >= $rate )
				{
					$_image->resizeXY( 0, $height );
				}

				$_image->cropFromCenter( $width, $height );

				$_image->save( NV_ROOTDIR . '/' . NV_TEMP_DIR, $basename );

				if( file_exists( NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . $basename ) )
				{
					$imgsource = NV_BASE_SITEURL . NV_TEMP_DIR . '/' . $basename;
				}
			}
		}
		elseif( nv_is_url( $homeimgfile ) )
		{
			$imgsource = $homeimgfile;
		}
		else
		{
			$imgsource = '';
		}
		return $imgsource;
	}
}

if( ! nv_function_exists( 'nv_tab_catid' ) )
{
	function nv_block_config_tab_catid( $module, $data_block, $lang_block )
	{
		global $site_mods;

		$html = '';
		$html .= '<tr>';
		$html .= '<td>' . $lang_block['config_catid'] . '</td>';
		$html .= '<td><select name="config_catid" class="form-control w200">';
		$html .= '<option value="0"> --- Chọn chuyên mục --- </option>';
		$sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $site_mods[$module]['module_data'] . '_cat ORDER BY sort ASC';
		$list = nv_db_cache( $sql, '', $module );
		foreach( $list as $l )
		{
			$xtitle_i = "";
			if( $l['lev'] > 0 )
			{
				$xtitle_i .= "&nbsp;&nbsp;&nbsp;|";
				for( $i = 1; $i <= $l['lev']; ++$i )
				{
					$xtitle_i .= "---";
				}
				$xtitle_i .= ">&nbsp;";
			}
			$xtitle_i .= $l['title'] . '-' . $l['catid'];
			$html .= '<option value="' . $l['catid'] . '" ' . ( ( $data_block['config_catid'] == $l['catid'] ) ? ' selected="selected"' : '' ) . '>' . $xtitle_i . '</option>';
		}
		$html .= '</select>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td>' . $lang_block['config_numrow'] . '</td>';
		$html .= '<td><input type="text" class="form-control w200" name="config_numrow" size="5" value="' . $data_block['config_numrow'] . '"/></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td>' . $lang_block['config_numcut'] . '</td>';
		$html .= '<td><input type="text" class="form-control w200" name="config_numcut" size="5" value="' . $data_block['config_numcut'] . '"/></td>';
		$html .= '</tr>';

		return $html;
	}

	function nv_block_config_tab_catid_submit( $module, $lang_block )
	{
		global $nv_Request;
		$return = array();
		$return['error'] = array();
		$return['config'] = array();
		$return['config']['config_catid'] = $nv_Request->get_int( 'config_catid', 'post', 0 );
		$return['config']['config_numrow'] = $nv_Request->get_int( 'config_numrow', 'post', 0 );

		return $return;
	}

	function nv_tab_catid( $block_config )
	{
		global $module_array_cat, $module_info, $site_mods, $module_config, $global_config, $db;
		$module = $block_config['module'];
		$mod_data = $site_mods[$module]['module_data'];
		$mod_file = $site_mods[$module]['module_file'];
		$numrow = ! empty( $block_config['config_numrow'] ) ? $block_config['config_numrow'] : 6;
		$show_no_image = $module_config[$module]['show_no_image'];

		if( isset( $module_array_cat[$block_config['config_catid']] ) && ! empty( $module_array_cat[$block_config['config_catid']] ) )
		{
			if( file_exists( NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $mod_file . '/block_tab_catid.tpl' ) )
			{
				$block_theme = $module_info['template'];
			}
			else
			{
				$block_theme = 'default';
			}

			$xtpl = new XTemplate( 'block_tab_catid.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/' . $mod_file );
			$xtpl->assign( 'NV_BASE_SITEURL', NV_BASE_SITEURL );
			$xtpl->assign( 'TEMPLATE', $block_theme );

			if( ! defined( 'LOAD_SCRIPT_TAB' ) )
			{
				$xtpl->parse( 'main.load_script_tab' );
				define( 'LOAD_SCRIPT_TAB', true );
			}

			$cat_news = $module_array_cat[$block_config['config_catid']];

			$array_cat = getParentSub( $cat_news['catid'] );

			$data_content = array();

			$cache_file = NV_LANG_DATA . '_' . md5( $array_cat ) . '_' . NV_CACHE_PREFIX . '.cache';

			if( ( $cache = nv_get_cache( $module, $cache_file ) ) != false )
			{
				$data_content = unserialize( $cache );
			}
			else
			{

				foreach( $array_cat as $_catid )
				{

					if( $module_array_cat[$_catid]['parentid'] == 0 )
					{
						$where = 'WHERE status= 1 AND catid IN ( ' . implode( ',', $array_cat ) . ' )';
					}
					else
					{
						$where = 'WHERE status= 1 AND catid=' . $_catid;
					}

					$result = $db->query( 'SELECT id, catid, title, alias, homeimgfile, homeimgthumb, homeimgalt, publtime FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_rows ' . $where . ' ORDER BY publtime DESC LIMIT ' . intval( $numrow ) );

					$data = array();

					while( $l = $result->fetch() )
					{
						$l['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $module_array_cat[$l['catid']]['alias'] . '/' . $l['alias'] . '-' . $l['id'] . $global_config['rewrite_exturl'];
						if( $l['homeimgthumb'] == 1 )
						{
							$l['thumb'] = NV_BASE_SITEURL . NV_FILES_DIR . '/' . $module . '/' . $l['homeimgfile'];
						}
						elseif( $l['homeimgthumb'] == 2 )
						{
							$l['thumb'] = creat_thumbs( $l['id'], $l['homeimgfile'], $module, 100, 100 );
						}
						elseif( $l['homeimgthumb'] == 3 )
						{
							$l['thumb'] = $l['homeimgfile'];
						}
						elseif( ! empty( $show_no_image ) )
						{
							$l['thumb'] = NV_BASE_SITEURL . $show_no_image;
						}
						else
						{
							$l['thumb'] = '';
						}
						$data[] = $l;

					}
					$data_content[$_catid] = array(
						'catid' => $_catid,
						'title' => $module_array_cat[$_catid]['title'],
						'alias' => $module_array_cat[$_catid]['alias'],
						'content' => $data );

				}
				$cache = serialize( $data_content );
				nv_set_cache( $module, $cache_file, $cache );
			}

			$xtpl->assign( 'TAB_TOTAL', count( $data_content ) );
			$xtpl->assign( 'TAB_NAME', $block_config['config_catid'] );

			if( ! empty( $data_content ) )
			{
				$a = 0;
				foreach( $data_content as $_catid => $data )
				{
					$xtpl->assign( 'NUM', $a );
					$xtpl->assign( 'CAT', $data );
					$xtpl->parse( 'main.loopcat' );

					foreach( $data['content'] as $loop )
					{
						$loop['style'] = ( $a == 0 ) ? 'display: block' : 'display: none';
						$xtpl->assign( 'LOOP', $loop );
						$xtpl->parse( 'main.loop.loopcontent' );

					}
					$xtpl->parse( 'main.loop' );
					++$a;
				}
			}
			$xtpl->parse( 'main' );
			return $xtpl->text( 'main' );
		}
	}

}

if( defined( 'NV_SYSTEM' ) )
{
	global $site_mods, $module_name, $global_array_cat, $module_array_cat;
	$module = $block_config['module'];
	if( isset( $site_mods[$module] ) )
	{
		if( $module == $module_name )
		{
			$module_array_cat = $global_array_cat;
			unset( $module_array_cat[0] );
		}
		else
		{
			$module_array_cat = array();
			$sql = 'SELECT catid, parentid, title, alias, viewcat, subcatid, numlinks, description, inhome, keywords, groups_view FROM ' . NV_PREFIXLANG . '_' . $site_mods[$module]['module_data'] . '_cat ORDER BY sort ASC';
			$list = nv_db_cache( $sql, 'catid', $module );
			foreach( $list as $l )
			{
				$module_array_cat[$l['catid']] = $l;
				$module_array_cat[$l['catid']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $l['alias'];
			}
		}
		$content = nv_tab_catid( $block_config );
	}
}
