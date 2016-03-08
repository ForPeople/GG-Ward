<?php
/**
 * @class ggward
 * @author GG
 * @brief gg ward widget
 * @version 0.1
 **/

class ggward extends WidgetHandler {
	function proc($args) {
		$logged_info = Context::get('logged_info');
		$module_info = Context::get('module_info');

		//로그인 전용
		if(!$logged_info) return;
		
		//게시판 view 전용노출
		if($module_info->module != 'board' || !Context::get('document_srl')) return;
		
		//현재 와드를 선택했는지 여부 판단
		$obj->ggmailing_member_srl = $logged_info->member_srl;
		$obj->ggmailing_document_srl = Context::get('document_srl');

		$output = executeQueryArray('widgets.ggward.getWardMember',$obj);

		if($output->data) $args->is_Member = 'A';
		else $args->is_Member = 'N';

		if(Context::get('ggstatus') == 'ggward_insert' && $args->is_Member == 'N') {
			
			$args->ggmailing_member_srl = $logged_info->member_srl;
			$args->ggmailing_nickname = $logged_info->nick_name;
			$args->ggmailing_email = $logged_info->email_address;
			$args->ggmailing_member_regdate = $logged_info->regdate;
			
			//$args->ggmailing_module_srl = $module_info->module_srl;
			$args->ggmailing_mid = $module_info->mid;
			$args->ggmailing_document_srl = Context::get('document_srl');
			//$args->ggmailing_comment_srl = Context::get('comment_srl');
			$args->regdate = date('YmdHis');

			executeQuery('widgets.ggward.insertWardMember',$args);

			$args->is_Member = 'A';
			
			$ggoutput = executeQueryArray('widgets.ggward.getWardMember',$gg);
			$args->is_Count = count($ggoutput->data);
			$returnUrl = getNotEncodedUrl('', 'mid', Context::get('mid'), 'document_srl', Context::get('document_srl'));
			echo '<script>alert("와드 설치가 완료되었습니다.");location.href="'.$returnUrl.'";</script>';

		} elseif(Context::get('ggstatus') == 'ggward_delete' && $args->is_Member == 'A') {
			$args->ggmailing_member_srl = $logged_info->member_srl;
			$args->ggmailing_document_srl = Context::get('document_srl');
			$ggoutput = executeQueryArray('widgets.ggward.getWardMember', $args);
			foreach($ggoutput->data as $key => $val) {
				if(!$val->ggmailing_module_srl) {
					$args->ggmailing_board_srl = $val->ggmailing_board_srl;
					executeQuery('widgets.ggward.deleteWardMember',$args);
				}
			}
			$args->is_Member = 'N';

			$returnUrl = getNotEncodedUrl('', 'mid', Context::get('mid'), 'document_srl', Context::get('document_srl'));
			echo '<script>alert("와드 제거가 완료되었습니다.");location.href="'.$returnUrl.'";</script>';
		}
		$gg = new stdClass();
		$gg->ggmailing_document_srl = Context::get('document_srl');
		$ggoutput = executeQueryArray('widgets.ggward.getWardMember',$gg);
		$args->is_Count = count($ggoutput->data);

		//위젯 옵션 설정
		if(!$args->before_btn_name) $args->before_btn_name = '와드 설치';
		if(!$args->after_btn_name) $args->after_btn_name = '와드 제거';
		if(!$args->align) $args->align = 'left';

		// 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
		Context::set('colorset', $args->colorset);
		Context::set('widget_info', $args);

		$tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
		$tpl_file = 'list';
		$oTemplate = &TemplateHandler::getInstance();
		return $oTemplate->compile($tpl_path, $tpl_file);
	}
}
?>
