<?php

use Cbit\Mc\RI\Service\Container;

$MESS["CRM_COMMON_TITLE"] = 'Name';
$MESS['CRM_TYPE_ITEM_FIELD_CREATED_BY'] = 'Requester';
$MESS['CRM_TYPE_ITEM_FIELD_ASSIGNED_BY_ID'] = 'Assigned to';
$MESS["CRM_TYPE_ITEM_FIELD_CREATED_BY_FEMININE"] = "Requester";
$MESS['CRM_TYPE_ITEM_FIELD_STAGE_ID'] = 'Stage';
$MESS["CRM_TYPE_ITEM_FIELD_CREATED_TIME"] = "Created on";
$MESS["CRM_TYPE_ITEM_FIELD_CREATED_TIME_FEMININE"] = "Date created";
$MESS["CRM_TYPE_ITEM_FIELD_MOVED_BY"] = "Moved by";
$MESS["CRM_TYPE_ITEM_FIELD_MOVED_BY_FEMININE"] = "Moved by";
$MESS["CRM_TYPE_ITEM_FIELD_MOVED_TIME"] = "Moved on";
$MESS["CRM_TYPE_ITEM_FIELD_MOVED_TIME_FEMININE"] = "Date moved";
$MESS["CRM_TYPE_ITEM_FIELD_PREVIOUS_STAGE_ID"] = "Previous stage";
$MESS["CRM_TYPE_ITEM_FIELD_STAGE_SEMANTIC_ID"] = "Stage group";
$MESS["CRM_TYPE_ITEM_FIELD_UPDATED_BY"] = "Updated by";
$MESS["CRM_TYPE_ITEM_FIELD_UPDATED_BY_FEMININE"] = "Updated by";
$MESS["CRM_TYPE_ITEM_FIELD_UPDATED_TIME"] = "Updated on";
$MESS["CRM_TYPE_ITEM_FIELD_UPDATED_TIME_FEMININE"] = "Date updated";
$MESS["CRM_TYPE_ITEM_FIELD_WEBFORM_ID"] = "Created by CRM form";
$MESS["CRM_TYPE_ITEM_FIELD_XML_ID"] = "External ID";
$MESS["CRM_TYPE_ITEM_FIELD_OPENED"] = "Available to everyone";

$MESS["UI_ENTITY_EDITOR_SAVE"] = Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForRi() ? "Save" : "Send request";

$MESS["CRM_FILTER_ITEMDATAPROVIDER_STAGE_SEMANTIC_FILTER_NAME"] = "Stage group";
$MESS["CRM_FILTER_ITEMDATAPROVIDER_STAGE_SEMANTIC_IN_WORK"] = "Process";
$MESS["CRM_FILTER_ITEMDATAPROVIDER_STAGE_SEMANTIC_SUCCESS"] = "Success";
$MESS["CRM_FILTER_ITEMDATAPROVIDER_STAGE_SEMANTIC_FAIL"] = "Fail";

$MESS['CRM_RI_ACTION_CANCEL'] = 'Cancel request';

$MESS['NEW_ITEM_CREATED_EVENT_TITLE']            = 'New request created';
$MESS['NEW_ITEM_CREATED_EVENT_TEXT_REQUESTER']   = 'Your request #TITLE# created successful';
$MESS['NEW_ITEM_CREATED_EVENT_LINK_REQUESTER']   = 'Your request <a href="#HREF#">#TITLE#</a> created successful';
$MESS['NEW_ITEM_CREATED_EVENT_TEXT_COORDINATOR'] = 'New request created - #TITLE#';
$MESS['NEW_ITEM_CREATED_EVENT_LINK_COORDINATOR'] = 'New request created - <a href="#HREF#">#TITLE#</a>';

$MESS['NEW_ITEM_UNASSIGNED_EVENT_TITLE']  = 'New request #TITLE# awaiting for distribution';
$MESS['NEW_ITEM_UNASSIGNED_EVENT_TEXT']   = 'The new request #TITLE# has not yet been assigned a responsible person. 
                                             Date/time of creation #DATE#';
$MESS['NEW_ITEM_UNASSIGNED_EVENT_LINK']   = 'The new request <a href="#HREF#">#TITLE#</a> has not yet been assigned a responsible person. 
                                             Date/time of creation #DATE#';

$MESS['ITEM_ASSIGNED_ADDED_EVENT_TITLE']  = 'Request #TITLE# assigned responsible';
$MESS['ITEM_ASSIGNED_ADDED_EVENT_TEXT']   = 'Your request #TITLE# has been assigned a responsible #NAME#';
$MESS['ITEM_ASSIGNED_ADDED_EVENT_LINK']   = 'Your request <a href="#HREF#">#TITLE#</a> has been assigned a responsible #NAME#';

$MESS['ITEM_LABOR_COST_FILLED_EVENT_TITLE']  = 'Planned execution time of the request #TITLE#';
$MESS['ITEM_LABOR_COST_FILLED_EVENT_TEXT']   = 'Your request #TITLE# will be completed #DATE#';
$MESS['ITEM_LABOR_COST_FILLED_EVENT_LINK']   = 'Your request <a href="#HREF#">#TITLE#</a> will be completed #DATE#';

$MESS['ITEM_COMMENT_ADDED_EVENT_TITLE']  = 'New comment in the request #TITLE#';
$MESS['ITEM_COMMENT_ADDED_EVENT_TEXT']   = 'Comment has been left for you in the request #TITLE#';
$MESS['ITEM_COMMENT_ADDED_EVENT_LINK']   = 'Comment has been left for you in the request <a href="#HREF#">#TITLE#</a><br>
                                            #TEXT#';

$MESS['ITEM_CANCELLED_EVENT_CREATED_BY'] = 'Requester';
$MESS['ITEM_CANCELLED_EVENT_ASSIGNED']   = 'Responsible';
$MESS['ITEM_CANCELLED_EVENT_TITLE']      = 'Request #TITLE# cancelled';
$MESS['ITEM_CANCELLED_EVENT_TEXT']       = '#TYPE# #NAME# cancelled request #TITLE# by reason: #REASON#. 
                                                Comment: #COMMENT#';
$MESS['ITEM_CANCELLED_EVENT_LINK']       = '#TYPE# #NAME# cancelled request <a href="#HREF#">#TITLE#</a> 
                                                by reason: #REASON#. 
                                                Comment: #COMMENT#';

$MESS['ITEM_COMPLETED_EVENT_TITLE']  = 'The result of the request #TITLE# is ready';
$MESS['ITEM_COMPLETED_EVENT_TEXT']   = "Request #TITLE# has been successfully completed with the following result: #RESULT#. 
                                        Please rate the performer's work
                                        (when evaluating the request, you will confirm that it has been completed)";
$MESS['ITEM_COMPLETED_EVENT_LINK']   = 'Request <a href="#HREF#">#TITLE#</a> has been successfully completed with the following result: #RESULT#. 
                                        <br>Please rate the performer`s work
                                        (when evaluating the request, you will confirm that it has been completed)';


