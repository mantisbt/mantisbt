<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

// NuSOAP already performs compression,
// so we prevent a double-compression.
// See issue #11868 for details
define( 'COMPRESSION_DISABLED', true);
ini_set( 'zlib.output_compression', false );

set_include_path( '../../library' );
require_once( 'nusoap/nusoap.php' );

# create server
$l_oServer = new soap_server();

# namespace
$t_namespace = 'http://futureware.biz/mantisconnect';

# wsdl generation
$l_oServer->debug_flag = false;
$l_oServer->configureWSDL( 'MantisConnect', $t_namespace );
$l_oServer->wsdl->schemaTargetNamespace = $t_namespace;
// The following will make the default encoding UTF-8 instead of ISO-8859-1
// WS-I Basic Profile requires UTF-8 or UTF-16 as the encoding for interoperabilty
// reasons.  This will correctly handle a large number of languages besides English.
$l_oServer->xml_encoding = "UTF-8";
$l_oServer->soap_defencoding = "UTF-8";
$l_oServer->decode_utf8 = false;

###
###  PUBLIC TYPES
###

### StringArray
$l_oServer->wsdl->addComplexType(
	'StringArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'xsd:string[]'
	)),
	'xsd:string'
);

### ObjectRef
$l_oServer->wsdl->addComplexType(
	'ObjectRef',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'	=>	array( 'name' => 'id',		'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'name'	=>	array( 'name' => 'name',	'type' => 'xsd:string', 	'minOccurs' => '0')
	)
);

### ObjectRefArray
$l_oServer->wsdl->addComplexType(
	'ObjectRefArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:ObjectRef[]'
	)),
	'tns:ObjectRef'
);

### AccountData
$l_oServer->wsdl->addComplexType(
	'AccountData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'		=>	array( 'name' => 'id',			'type' => 'xsd:integer',	'minOccurs' => '0'),
		'name'		=>	array( 'name' => 'name',		'type' => 'xsd:string',	'minOccurs' => '0'),
		'real_name'	=>	array( 'name' => 'real_name',	'type' => 'xsd:string',	'minOccurs' => '0'),
		'email'		=>	array( 'name' => 'email',		'type' => 'xsd:string',	'minOccurs' => '0')
	)
);

### AccountDataArray
$l_oServer->wsdl->addComplexType(
	'AccountDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:AccountData[]'
	)),
	'tns:AccountData'
);

### AttachmentData
$l_oServer->wsdl->addComplexType(
	'AttachmentData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'				=>	array( 'name' => 'id',				'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'filename'			=>	array( 'name' => 'filename',		'type' => 'xsd:string', 	'minOccurs' => '0'),
		'size'				=>	array( 'name' => 'size',			'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'content_type'		=>	array( 'name' => 'content_type',	'type' => 'xsd:string', 	'minOccurs' => '0'),
		'date_submitted'	=>	array( 'name' => 'date_submitted',	'type' => 'xsd:dateTime', 	'minOccurs' => '0'),
		'download_url'		=>	array( 'name' => 'download_url',	'type' => 'xsd:anyURI', 	'minOccurs' => '0'),
		'user_id'		    =>	array( 'name' => 'user_id',			'type' => 'xsd:integer', 	'minOccurs' => '0')
	)
);

### AttachmentDataArray
$l_oServer->wsdl->addComplexType(
	'AttachmentDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:AttachmentData[]'
	)),
	'tns:AttachmentData'
);

### ProjectAttachmentData
$l_oServer->wsdl->addComplexType(
	'ProjectAttachmentData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'				=>	array( 'name' => 'id',				'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'filename'			=>	array( 'name' => 'filename',		'type' => 'xsd:string', 	'minOccurs' => '0'),
		'title'				=>	array( 'name' => 'title',			'type' => 'xsd:string', 	'minOccurs' => '0'),
		'description'		=>	array( 'name' => 'description',		'type' => 'xsd:string', 	'minOccurs' => '0'),
		'size'				=>	array( 'name' => 'size',			'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'content_type'		=>	array( 'name' => 'content_type',	'type' => 'xsd:string', 	'minOccurs' => '0'),
		'date_submitted'	=>	array( 'name' => 'date_submitted',	'type' => 'xsd:dateTime', 	'minOccurs' => '0'),
		'download_url'		=>	array( 'name' => 'download_url',	'type' => 'xsd:anyURI', 	'minOccurs' => '0'),
		'user_id'		    =>	array( 'name' => 'user_id',			'type' => 'xsd:integer', 	'minOccurs' => '0')
	)
);

### ProjectAttachmentDataArray
$l_oServer->wsdl->addComplexType(
	'ProjectAttachmentDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:ProjectAttachmentData[]'
	)),
	'tns:ProjectAttachmentData'
);

### RelationshipData
$l_oServer->wsdl->addComplexType(
	'RelationshipData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'		=>	array( 'name' => 'id',			'type' => 'xsd:integer',	'minOccurs' => '0'),
		'type'		=>	array( 'name' => 'type',		'type' => 'tns:ObjectRef', 	'minOccurs' => '0'),
		'target_id'	=>	array( 'name' => 'target_id',	'type' => 'xsd:integer', 	'minOccurs' => '0')
	)
);

### RelationshipDataArray
$l_oServer->wsdl->addComplexType(
	'RelationshipDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:RelationshipData[]'
	)),
	'tns:RelationshipData'
);

### IssueNoteData
$l_oServer->wsdl->addComplexType(
	'IssueNoteData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'				=>	array( 'name' => 'id',				'type' => 'xsd:integer', 'minOccurs' => '0'),
		'reporter'			=>	array( 'name' => 'reporter',		'type' => 'tns:AccountData', 'minOccurs' => '0'),
		'text'				=>	array( 'name' => 'text',			'type' => 'xsd:string', 'minOccurs' => '0'),
		'view_state'		=>	array( 'name' => 'view_state',		'type' => 'tns:ObjectRef', 'minOccurs' => '0'),
		'date_submitted'	=>	array( 'name' => 'date_submitted',	'type' => 'xsd:dateTime', 'minOccurs' => '0'),
		'last_modified'		=>	array( 'name' => 'last_modified',	'type' => 'xsd:dateTime', 'minOccurs' => '0'),
		'time_tracking'		=> 	array( 'name' => 'time_tracking',	'type' => 'xsd:integer', 'minOccurs' => '0'),
	    'note_type'			=>  array( 'name' => 'note_type',		'type' => 'xsd:integer', 'minOccurs' => '0'),
		'note_attr'			=>  array( 'name' => 'note_attr',		'type' => 'xsd:string', 'minOccurs' => '0')
	)
);

### IssueNoteDataArray
$l_oServer->wsdl->addComplexType(
	'IssueNoteDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:IssueNoteData[]'
	)),
	'tns:IssueNoteData'
);

### IssueData
$l_oServer->wsdl->addComplexType(
	'IssueData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'			=>	array( 'name' => 'id',				'type' => 'xsd:integer', 	'minOccurs' => '0' ),
		'view_state'	=>	array( 'name' => 'view_state',		'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'last_updated'	=>	array( 'name' => 'last_updated',	'type' => 'xsd:dateTime', 	'minOccurs' => '0' ),

		'project'	=>	array( 'name' => 'project',		'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'category'	=>	array( 'name' => 'category',	'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'priority'	=>	array( 'name' => 'priority',	'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'severity'	=>	array( 'name' => 'severity',	'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'status'	=>	array( 'name' => 'status',		'type' => 'tns:ObjectRef',	'minOccurs' => '0' ),

		'reporter'			=>	array( 'name' => 'reporter',		'type' => 'tns:AccountData',	'minOccurs' => '0' ),
		'summary'			=>	array( 'name' => 'summary',			'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'version'			=>	array( 'name' => 'version',			'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'build'				=>	array( 'name' => 'build',			'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'platform'			=>	array( 'name' => 'platform',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'os'				=>	array( 'name' => 'os',				'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'os_build'			=>	array( 'name' => 'os_build',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'reproducibility'	=>	array( 'name' => 'reproducibility', 'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'date_submitted'	=>	array( 'name' => 'date_submitted',	'type' => 'xsd:dateTime', 	'minOccurs' => '0' ),

		'sponsorship_total' =>	array( 'name' => 'sponsorship_total',	'type' => 'xsd:integer', 	'minOccurs' => '0' ),

		'handler'		=>	array( 'name' => 'handler',		'type' => 'tns:AccountData', 	'minOccurs' => '0' ),
		'projection'	=>	array( 'name' => 'projection',	'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'eta'			=>	array( 'name' => 'eta',			'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),

		'resolution'		=>	array( 'name' => 'resolution',		'type' => 'tns:ObjectRef', 	'minOccurs' => '0' ),
		'fixed_in_version'	=>	array( 'name'=>'fixed_in_version',	'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'target_version'	=>	array( 'name'=>'target_version',	'type' => 'xsd:string', 	'minOccurs' => '0' ),

		'description'				=>	array( 'name' => 'description',				'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'steps_to_reproduce'		=>	array( 'name' => 'steps_to_reproduce',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'additional_information'	=>	array( 'name' => 'additional_information',	'type' => 'xsd:string', 	'minOccurs' => '0' ),

		'attachments'				=>	array( 'name' => 'attachments', 			'type' => 'tns:AttachmentDataArray', 	'minOccurs' => '0' ),
		'relationships'				=>	array( 'name' => 'relationships',			'type' => 'tns:RelationshipDataArray', 	'minOccurs' => '0' ),
		'notes'						=>	array( 'name' => 'notes',					'type' => 'tns:IssueNoteDataArray', 	'minOccurs' => '0' ),
		'custom_fields'				=>  array( 'name' => 'custom_fields',			'type' => 'tns:CustomFieldValueForIssueDataArray', 	'minOccurs' => '0' ),
		'due_date'					=>  array( 'name' => 'due_date',				'type' => 'xsd:dateTime', 	'minOccurs' => '0' ),
	    'monitors'					=>  array( 'name' => 'monitors',                'type' => 'tns:AccountDataArray', 'minOccurs' => '0'),
	    'sticky'    				=>  array( 'name' => 'sticky',                  'type' => 'xsd:boolean', 'minOccurs' => '0'),
	    'tags'						=>  array( 'name' => 'tags',                	'type' => 'tns:ObjectRefArray', 'minOccurs' => '0')
	)
);

### IssueDataArray
$l_oServer->wsdl->addComplexType(
	'IssueDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:IssueData[]'
	)),
	'tns:IssueData'
);


### IssueHeaderData
$l_oServer->wsdl->addComplexType(
	'IssueHeaderData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'			=>	array( 'name' => 'id',				'type' => 'xsd:integer' ),
		'view_state'	=>	array( 'name' => 'view_state',		'type' => 'xsd:integer' ),
		'last_updated'	=>	array( 'name' => 'last_updated',	'type' => 'xsd:dateTime' ),

		'project'	=>	array( 'name' => 'project',		'type' => 'xsd:integer' ),
		'category'	=>	array( 'name' => 'category',	'type' => 'xsd:string' ),
		'priority'	=>	array( 'name' => 'priority',	'type' => 'xsd:integer' ),
		'severity'	=>	array( 'name' => 'severity',	'type' => 'xsd:integer' ),
		'status'	=>	array( 'name' => 'status',		'type' => 'xsd:integer' ),

		'reporter'			=>	array( 'name' => 'reporter',		'type' => 'xsd:integer' ),
		'summary'			=>	array( 'name' => 'summary',			'type' => 'xsd:string' ),
		'handler'		=>	array( 'name' => 'handler',		'type' => 'xsd:integer' ),
		'resolution'		=>	array( 'name' => 'resolution',		'type' => 'xsd:integer' ),

		'attachments_count'	=>	array( 'name' => 'attachments_count', 	'type' => 'xsd:integer' ),
		'notes_count'	=>	array( 'name' => 'notes_count', 	'type' => 'xsd:integer' ),
	)
);

### IssueHeaderDataArray
$l_oServer->wsdl->addComplexType(
	'IssueHeaderDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:IssueHeaderData[]'
	)),
	'tns:IssueHeaderData'
);

### ProjectData
$l_oServer->wsdl->addComplexType(
	'ProjectData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'			=>	array( 'name' => 'id',			'type' => 'xsd:integer',	'minOccurs' => '0' ),
		'name'			=>	array( 'name' => 'name',		'type' => 'xsd:string',	'minOccurs' => '0' ),
		'status'		=>	array( 'name' => 'status',		'type' => 'tns:ObjectRef',	'minOccurs' => '0' ),
		'enabled'		=>	array( 'name' => 'enabled',		'type' => 'xsd:boolean',	'minOccurs' => '0' ),
		'view_state'	=>	array( 'name' => 'view_state',	'type' => 'tns:ObjectRef',	'minOccurs' => '0' ),
		'access_min'	=>	array( 'name' => 'access_min',	'type' => 'tns:ObjectRef',	'minOccurs' => '0' ),
		'file_path'		=>	array( 'name' => 'file_path',	'type' => 'xsd:string',	'minOccurs' => '0' ),
		'description'	=>	array( 'name' => 'description',	'type' => 'xsd:string',	'minOccurs' => '0' ),
		'subprojects'	=>	array( 'name' => 'subprojects',	'type' => 'tns:ProjectDataArray', 'minOccurs' => '0' ),
		'inherit_global'		=>	array( 'name' => 'inherit_global',		'type' => 'xsd:boolean',	'minOccurs' => '0' )
	)
);

### ProjectDataArray
$l_oServer->wsdl->addComplexType(
	'ProjectDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:ProjectData[]'
	)),
	'tns:ProjectData'
);

### ProjectVersionData
$l_oServer->wsdl->addComplexType(
	'ProjectVersionData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'			=>	array( 'name' => 'id',			'type' => 'xsd:integer', 	'minOccurs' => '0' ),
		'name'			=>	array( 'name' => 'name',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'project_id'	=>	array( 'name' => 'project_id',	'type' => 'xsd:integer', 	'minOccurs' => '0' ),
		'date_order'	=>	array( 'name' => 'date_order',	'type' => 'xsd:dateTime', 	'minOccurs' => '0' ),
		'description'	=>	array( 'name' => 'description',	'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'released'		=>	array( 'name' => 'released',	'type' => 'xsd:boolean', 	'minOccurs' => '0' ),
		'obsolete'		=>	array( 'name' => 'obsolete',	'type' => 'xsd:boolean', 	'minOccurs' => '0' )
	)
);

### ProjectVersionDataArray
$l_oServer->wsdl->addComplexType(
	'ProjectVersionDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:ProjectVersionData[]'
	)),
	'tns:ProjectVersionData'
);

### FilterData
$l_oServer->wsdl->addComplexType(
	'FilterData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'id'			=>	array( 'name' => 'id',				'type' => 'xsd:integer', 	'minOccurs' => '0' ),
		'owner'			=>	array( 'name' => 'owner',			'type' => 'tns:AccountData', 	'minOccurs' => '0' ),
		'project_id'	=>	array( 'name' => 'project_id',		'type' => 'xsd:integer', 	'minOccurs' => '0' ),
		'is_public'		=>	array( 'name' => 'is_public',		'type' => 'xsd:boolean', 	'minOccurs' => '0' ),
		'name'			=>	array( 'name' => 'name',			'type' => 'xsd:string', 	'minOccurs' => '0' ),
		'filter_string'	=>	array( 'name' => 'filter_string',	'type' => 'xsd:string', 	'minOccurs' => '0' ),
	    'url'           =>  array( 'name' => 'url',				'type' => 'xsd:string', 	'minOccurs' => '0' )
	)
);

### FilterDataArray
$l_oServer->wsdl->addComplexType(
	'FilterDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:FilterData[]'
	)),
	'tns:FilterData'
);

### CustomFieldDefinitionData
$l_oServer->wsdl->addComplexType(
	'CustomFieldDefinitionData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'field'				=>	array( 'name' => 'field',			'type' => 'tns:ObjectRef', 	'minOccurs' => '0'),
		'type'				=>	array( 'name' => 'type',			'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'possible_values'	=>	array( 'name' => 'possible_values',	'type' => 'xsd:string', 	'minOccurs' => '0'),
		'default_value'		=>	array( 'name' => 'default_value',	'type' => 'xsd:string', 	'minOccurs' => '0'),
		'valid_regexp'		=>	array( 'name' => 'valid_regexp',	'type' => 'xsd:string', 	'minOccurs' => '0'),
		'access_level_r'	=>	array( 'name' => 'access_level_r',	'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'access_level_rw'	=>	array( 'name' => 'access_level_rw',	'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'length_min'		=>	array( 'name' => 'length_min',		'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'length_max'		=>	array( 'name' => 'length_max',		'type' => 'xsd:integer', 	'minOccurs' => '0'),
		'advanced'              =>      array( 'name' => 'advanced',            'type' => 'xsd:boolean',        'minOccurs' => '0'),
		'display_report'	=>	array( 'name' => 'display_report',	'type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'display_update'	=>	array( 'name' => 'display_update',	'type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'display_resolved'	=>	array( 'name' => 'display_resolved','type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'display_closed'	=>	array( 'name' => 'display_closed',	'type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'require_report'	=>	array( 'name' => 'require_report',	'type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'require_update'	=>	array( 'name' => 'require_update',	'type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'require_resolved'	=>	array( 'name' => 'require_resolved','type' => 'xsd:boolean', 	'minOccurs' => '0'),
		'require_closed'	=>	array( 'name' => 'require_closed',	'type' => 'xsd:boolean', 	'minOccurs' => '0')
	)
);

### CustomFieldDefinitionDataArray
$l_oServer->wsdl->addComplexType(
	'CustomFieldDefinitionDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:CustomFieldDefinitionData[]'
	)),
	'tns:CustomFieldDefinitionData'
);

### CustomFieldLinkForProjectData
$l_oServer->wsdl->addComplexType(
	'CustomFieldLinkForProjectData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'field'				=>	array( 'name' => 'field',			'type' => 'tns:ObjectRef', 	'minOccurs' => '0'),
		'sequence'			=>	array( 'name' => 'sequence',		'type' => 'xsd:byte', 	'minOccurs' => '0')
	)
);

### CustomFieldLinkForProjectDataArray
$l_oServer->wsdl->addComplexType(
	'CustomFieldLinkForProjectDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:CustomFieldLinkForProjectData[]'
	)),
	'tns:CustomFieldLinkForProjectData'
);

### CustomFieldValueForIssueData
$l_oServer->wsdl->addComplexType(
	'CustomFieldValueForIssueData',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'field'				=>	array( 'name' => 'field',			'type' => 'tns:ObjectRef', 	'minOccurs' => '0'),
		'value'				=>	array( 'name' => 'value',			'type' => 'xsd:string', 	'minOccurs' => '0')
	)
);

### CustomFieldValueForIssueDataArray
$l_oServer->wsdl->addComplexType(
	'CustomFieldValueForIssueDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
		'ref'				=> 'SOAP-ENC:arrayType',
		'wsdl:arrayType'	=> 'tns:CustomFieldValueForIssueData[]'
	)),
	'tns:CustomFieldValueForIssueData'
);

### TagData
$l_oServer->wsdl->addComplexType(
	'TagData',
	'complexType',
	'struct',
	'all',
	'',
	array(
			'id'			=>	array( 'name' => 'id',				'type' => 'xsd:integer', 	'minOccurs' => '0' ),
			'user_id'		=>	array( 'name' => 'user_id',			'type' => 'tns:AccountData', 	'minOccurs' => '0' ),
			'name'			=>	array( 'name' => 'name',			'type' => 'xsd:string', 	'minOccurs' => '0' ),
			'description'	=>	array( 'name' => 'description',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
			'date_created'	=>	array( 'name' => 'date_created',	'type' => 'xsd:dateTime', 	'minOccurs' => '0' ),
		    'date_updated'  =>  array( 'name' => 'date_updated',	'type' => 'xsd:dateTime', 	'minOccurs' => '0' )
	)
);

### TagDataArray
$l_oServer->wsdl->addComplexType(
	'TagDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
			'ref'				=> 'SOAP-ENC:arrayType',
			'wsdl:arrayType'	=> 'tns:TagData[]'
		)
	),
	'tns:TagData'
);

### TagDataSearchResult
$l_oServer->wsdl->addComplexType(
	'TagDataSearchResult',
	'complexType',
	'struct',
	'all',
	'',
	array(
				'results'		=>	array( 'name' => 'results',			'type' => 'tns:TagDataArray', 	'minOccurs' => '0' ),
			    'total_results' =>  array( 'name' => 'total_results',	'type' => 'xsd:integer', 		'minOccurs' => '0' )
	)
);

### ProfileData
$l_oServer->wsdl->addComplexType(
	'ProfileData',
	'complexType',
	'struct',
	'all',
	'',
	array(
				'id'			=>	array( 'name' => 'id',				'type' => 'xsd:integer', 	'minOccurs' => '0' ),
				'user_id'		=>	array( 'name' => 'user_id',			'type' => 'tns:AccountData', 	'minOccurs' => '0' ),
				'platform'		=>	array( 'name' => 'platform',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
				'os'			=>	array( 'name' => 'os',				'type' => 'xsd:string', 	'minOccurs' => '0' ),
				'os_build'		=>	array( 'name' => 'os_build',		'type' => 'xsd:string', 	'minOccurs' => '0' ),
			    'description'  	=>  array( 'name' => 'description',		'type' => 'xsd:string', 	'minOccurs' => '0' )
	)
);

### ProfileDataArray
$l_oServer->wsdl->addComplexType(
	'ProfileDataArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array(
				'ref'				=> 'SOAP-ENC:arrayType',
				'wsdl:arrayType'	=> 'tns:ProfileData[]'
	)
	),
	'tns:ProfileData'
);


### ProfileDataSearchResult
$l_oServer->wsdl->addComplexType(
	'ProfileDataSearchResult',
	'complexType',
	'struct',
	'all',
	'',
	array(
					'results'		=>	array( 'name' => 'results',			'type' => 'tns:ProfileDataArray', 	'minOccurs' => '0' ),
				    'total_results' =>  array( 'name' => 'total_results',	'type' => 'xsd:integer', 			'minOccurs' => '0' )
	)
);
###
###  PUBLIC METHODS
###

### mc_version
$l_oServer->register( 'mc_version',
	array(),
	array(
		'return'	=>	'xsd:string'
	),
	$t_namespace
);

###
###  PUBLIC METHODS (defined in mc_enum_api.php)
###

### mc_enum_status
$l_oServer->register( 'mc_enum_status',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for statuses.'
);

### mc_enum_priorities
$l_oServer->register( 'mc_enum_priorities',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for priorities.'
);

### mc_enum_severities
$l_oServer->register( 'mc_enum_severities',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for severities.'
);

### mc_enum_reproducibilities
$l_oServer->register( 'mc_enum_reproducibilities',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for reproducibilities.'
);

### mc_enum_projections
$l_oServer->register( 'mc_enum_projections',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for projections.'
);

### mc_enum_etas
$l_oServer->register( 'mc_enum_etas',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for ETAs.'
);

### mc_enum_resolutions
$l_oServer->register( 'mc_enum_resolutions',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for resolutions.'
);

### mc_enum_access_levels
$l_oServer->register( 'mc_enum_access_levels',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for access levels.'
);

### mc_enum_project_status
$l_oServer->register( 'mc_enum_project_status',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for project statuses.'
);

### mc_enum_project_view_states
$l_oServer->register( 'mc_enum_project_view_states',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for project view states.'
);

### mc_enum_view_states
$l_oServer->register( 'mc_enum_view_states',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for view states.'
);

### mc_enum_custom_field_types
$l_oServer->register( 'mc_enum_custom_field_types',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ObjectRefArray'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for custom field types.'
);

### mc_enum_get (should vanish as it has been replaced by more-high level versions)
$l_oServer->register( 'mc_enum_get',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'enumeration'	=>	'xsd:string'
	),
	array(
		'return'	=>	'xsd:string'
	),
	$t_namespace,
	false, false, false,
	'Get the enumeration for the specified enumeration type.'
);

###
###  PUBLIC METHODS (defined in mc_issue_api.php)
###

### mc_issue_exists
$l_oServer->register( 'mc_issue_exists',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Check there exists an issue with the specified id.'
);

### mc_issue_get
$l_oServer->register( 'mc_issue_get',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:IssueData'
	),
	$t_namespace,
	false, false, false,
	'Get the issue with the specified id.'
);

### mc_issue_get_biggest_id
$l_oServer->register( 'mc_issue_get_biggest_id',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'project_id'=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Get the latest submitted issue in the specified project.'
);

### mc_issue_get_id_from_summary (should be replaced with a more general search that returns matching issues directly)
$l_oServer->register( 'mc_issue_get_id_from_summary',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'summary'	=>	'xsd:string'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Get the id of the issue with the specified summary.'
);

### mc_issue_add
$l_oServer->register( 'mc_issue_add',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue'		=>	'tns:IssueData'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Submit the specified issue details.'
);

### mc_issue_update
$l_oServer->register( 'mc_issue_update',
	array(
		'username' => 'xsd:string',
		'password' => 'xsd:string',
		'issueId' => 'xsd:integer',
		'issue' => 'tns:IssueData'
	),
	array(
		'return' => 'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Update Issue method.'
);

$l_oServer->register( 'mc_issue_set_tags',
	array(
				'username'			=>	'xsd:string',
				'password'			=>	'xsd:string',
				'issue_id'			=>	'xsd:integer',
				'tags'				=>  'tns:TagDataArray'
	),
	array(
				'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Sets the tags for a specified issue.'
);


### mc_issue_delete
$l_oServer->register( 'mc_issue_delete',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Delete the issue with the specified id.'
);

### mc_issue_note_add
$l_oServer->register( 'mc_issue_note_add',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_id'	=>	'xsd:integer',
		'note' 		=>	'tns:IssueNoteData'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Submit a new note.'
);

### mc_issue_note_delete
$l_oServer->register( 'mc_issue_note_delete',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_note_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Delete the note with the specified id.'
);

### mc_issue_note_update
$l_oServer->register( 'mc_issue_note_update',
    array(
        'username'  =>  'xsd:string',
        'password'  =>  'xsd:string',
        'note'      =>  'tns:IssueNoteData'
    ),
    array(
        'return'    =>  'xsd:boolean'
    ),
    $t_namespace,
    false, false, false,
    'Update a specific note of a specific issue.'
);

### mc_issue_relationship_add
$l_oServer->register( 'mc_issue_relationship_add',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'issue_id'		=>	'xsd:integer',
		'relationship'	=>	'tns:RelationshipData'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Submit a new relationship.'
);

### mc_issue_relationship_delete
$l_oServer->register( 'mc_issue_relationship_delete',
	array(
		'username'			=>	'xsd:string',
		'password'			=>	'xsd:string',
		'issue_id'			=>	'xsd:integer',
		'relationship_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Delete the relationship for the specified issue.'
);

### mc_issue_attachment_add
$l_oServer->register( 'mc_issue_attachment_add',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_id'	=>	'xsd:integer',
		'name'		=>	'xsd:string',
		'file_type'	=>	'xsd:string',
		'content'	=>	'xsd:base64Binary'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Submit a new issue attachment.'
);

### mc_issue_attachment_delete
$l_oServer->register( 'mc_issue_attachment_delete',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_attachment_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Delete the issue attachment with the specified id.'
);

### mc_attachment_get
$l_oServer->register( 'mc_issue_attachment_get',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'issue_attachment_id'			=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:base64Binary'
	),
	$t_namespace,
	false, false, false,
	'Get the data for the specified issue attachment.'
);

###
###  PUBLIC METHODS (defined in mc_project_api.php)
###

### mc_project_add
$l_oServer->register( 'mc_project_add',
	array(
		'username' => 'xsd:string',
		'password' => 'xsd:string',
		'project' => 'tns:ProjectData'
	),
	array(
		'return' => 'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Add a new project to the tracker (must have admin privileges)'
);

### mc_project_delete
$l_oServer->register( 'mc_project_delete',
	array(
		'username' => 'xsd:string',
		'password' => 'xsd:string',
		'project_id' => 'xsd:integer'
	),
	array(
		'return' => 'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Add a new project to the tracker (must have admin privileges)'
);

### mc_project_update
$l_oServer->register( 'mc_project_update',
        array(
                'username' => 'xsd:string',
                'password' => 'xsd:string',
                'project_id' => 'xsd:integer',
                'project' => 'tns:ProjectData'
        ),
        array(
                'return' => 'xsd:boolean'
        ),
        $t_namespace,
        false, false, false,
        'Update a specific project to the tracker (must have admin privileges)'
);

### mc_project_get_id_from_name
$l_oServer->register( 'mc_project_get_id_from_name',
	array(
		'username' => 'xsd:string',
		'password' => 'xsd:string',	
		'project_name' => 'xsd:string'		
	),
	array(
		'return' => 'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Get the id of the project with the specified name.'
);

### mc_project_get_issues
$l_oServer->register( 'mc_project_get_issues',
	array(
		'username' => 'xsd:string',
		'password' => 'xsd:string',
		'project_id' => 'xsd:integer',
		'page_number' => 'xsd:integer',
		'per_page' => 'xsd:integer'
	),
	array(
		'return' => 'tns:IssueDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the issues that match the specified project id and paging details.'
);

### mc_project_get_issue_headers
$l_oServer->register( 'mc_project_get_issue_headers',
	array(
		'username' => 'xsd:string',
		'password' => 'xsd:string',
		'project_id' => 'xsd:integer',
		'page_number' => 'xsd:integer',
		'per_page' => 'xsd:integer'
	),
	array(
		'return' => 'tns:IssueHeaderDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the issue headers that match the specified project id and paging details.'
);

### mc_project_get_users
$l_oServer->register( 'mc_project_get_users',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'project_id'	=>	'xsd:integer',
		'access'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:AccountDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get appropriate users assigned to a project by access level.'
);

### mc_projects_get_user_accessible
$l_oServer->register( 'mc_projects_get_user_accessible',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string'
	),
	array(
		'return'	=>	'tns:ProjectDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the list of projects that are accessible to the logged in user.'
);

### mc_project_get_categories
$l_oServer->register( 'mc_project_get_categories',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:StringArray'
	),
	$t_namespace,
	false, false, false,
	'Get the categories belonging to the specified project.'
);

### mc_project_add_category
$l_oServer->register( 'mc_project_add_category',
        array(
                'username'              =>      'xsd:string',
                'password'              =>      'xsd:string',
                'project_id'            =>      'xsd:integer',
                'p_category_name'       =>      'xsd:string',
        ),
        array(
                'return'                =>      'xsd:integer'
        ),
        $t_namespace,
        false, false, false,
        'Add a category of specific project.'
);


### mc_project_delete_category
$l_oServer->register( 'mc_project_delete_category',
        array(
                'username'              =>      'xsd:string',
                'password'              =>      'xsd:string',
                'project_id'            =>      'xsd:integer',
                'p_category_name'       =>      'xsd:string',
        ),
        array(
                'return'                =>      'xsd:integer'
        ),
        $t_namespace,
        false, false, false,
        'Delete a category of specific project.'
);


### mc_project_rename_category_by_name
$l_oServer->register( 'mc_project_rename_category_by_name',
        array(
                'username'              =>      'xsd:string',
                'password'              =>      'xsd:string',
                'project_id'            =>      'xsd:integer',
                'p_category_name'       =>      'xsd:string',
                'p_category_name_new'   =>      'xsd:string',
                'p_assigned_to'         =>      'xsd:integer',
        ),
        array(
                'return'                =>      'xsd:integer'
        ),
        $t_namespace,
        false, false, false,
        'Rename a category of specific project.'
);

### mc_project_get_versions
$l_oServer->register( 'mc_project_get_versions',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:ProjectVersionDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the versions belonging to the specified project.'
);

### mc_project_version_add
$l_oServer->register( 'mc_project_version_add',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'version'		=>	'tns:ProjectVersionData'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Submit the specified version details.'
);

### mc_project_version_update
$l_oServer->register( 'mc_project_version_update',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'version_id'	=>	'xsd:integer',
		'version'		=>	'tns:ProjectVersionData'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Update version method.'
);

### mc_project_version_delete
$l_oServer->register( 'mc_project_version_delete',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'version_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Delete the version with the specified id.'
);

### mc_project_get_released_versions
$l_oServer->register( 'mc_project_get_released_versions',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:ProjectVersionDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the released versions that belong to the specified project.'
);

### mc_project_get_unreleased_versions
$l_oServer->register( 'mc_project_get_unreleased_versions',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:ProjectVersionDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the unreleased version that belong to the specified project.'
);

### mc_project_get_attachments
$l_oServer->register( 'mc_project_get_attachments',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:ProjectAttachmentDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the attachments that belong to the specified project.'
);

### mc_project_get_custom_fields
$l_oServer->register( 'mc_project_get_custom_fields',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:CustomFieldDefinitionDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the custom fields that belong to the specified project.'
);

### mc_project_attachment_get
$l_oServer->register( 'mc_project_attachment_get',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_attachment_id'			=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:base64Binary'
	),
	$t_namespace,
	false, false, false,
	'Get the data for the specified project attachment.'
);

### mc_issue_attachment_add
$l_oServer->register( 'mc_project_attachment_add',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer',
		'name'			=>	'xsd:string',
		'title'			=>	'xsd:string',
		'description'	=>	'xsd:string',
		'file_type'		=>	'xsd:string',
		'content'		=>	'xsd:base64Binary'
	),
	array(
		'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Submit a new project attachment.'
);

### mc_project_attachment_delete
$l_oServer->register( 'mc_project_attachment_delete',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'project_attachment_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Delete the project attachment with the specified id.'
);

### mc_project_get_subprojects
$l_oServer->register( 'mc_project_get_all_subprojects',
    array(
        'username'    =>  'xsd:string',
        'password'    =>  'xsd:string',
        'project_id'  =>  'xsd:integer'
    ),
    array(
        'return'      =>  'tns:StringArray'
    ),
    $t_namespace,
    false, false, false,
    'Get the subprojects ID of a specific project.'
);


###
###  PUBLIC METHODS (defined in mc_filter_api.php)
###

### mc_filter_get
$l_oServer->register( 'mc_filter_get',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:FilterDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the filters defined for the specified project.'
);

### mc_filter_get_issues
$l_oServer->register( 'mc_filter_get_issues',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer',
		'filter_id'		=>	'xsd:integer',
		'page_number'	=>	'xsd:integer',
		'per_page'		=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:IssueDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the issues that match the specified filter and paging details.'
);

### mc_filter_get_issue_headers
$l_oServer->register( 'mc_filter_get_issue_headers',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer',
		'filter_id'		=>	'xsd:integer',
		'page_number'	=>	'xsd:integer',
		'per_page'		=>	'xsd:integer'
	),
	array(
		'return' => 'tns:IssueHeaderDataArray'
	),
	$t_namespace,
	false, false, false,
	'Get the issue headers that match the specified filter and paging details.'
);

### mc_config_get_string
$l_oServer->register( 'mc_config_get_string',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'config_var'	=>	'xsd:string'
	),
	array(
		'return'	=>	'xsd:string'
	),
	$t_namespace,
	false, false, false,
	'Get the value for the specified configuration variable.'
);

### mc_issue_checkin
$l_oServer->register( 'mc_issue_checkin',
	array(
		'username'	=>	'xsd:string',
		'password'	=>	'xsd:string',
		'issue_id'	=>	'xsd:integer',
		'comment'	=>	'xsd:string',
		'fixed'		=>	'xsd:boolean'
	),
	array(
		'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Notifies MantisBT of a check-in for the issue with the specified id.'
);

###
###  PUBLIC METHODS (defined in mc_user_pref_api.php)
###

### mc_user_pref_get_pref
$l_oServer->register( 'mc_user_pref_get_pref',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'project_id'	=>	'xsd:integer',
		'pref_name'		=>	'xsd:string'
	),
	array(
		'return'	=>	'xsd:string'
	),
	$t_namespace,
	false, false, false,
	'Get the value for the specified user preference.'
);

###
###  PUBLIC METHODS (defined in mc_user_profile_api.php)
###


### mc_user_profiles_get_all
$l_oServer->register( 'mc_user_profiles_get_all',
	array(
		'username'		=>	'xsd:string',
		'password'		=>	'xsd:string',
		'page_number'	=>	'xsd:integer',
		'per_page'		=>	'xsd:integer'
	),
	array(
		'return'	=>	'tns:ProfileDataSearchResult'
	),
	$t_namespace,
	false, false, false,
	'Get profiles available to the current user.'
);

###
###  PUBLIC METHODS (defined in mc_tag_api.php)
###

$l_oServer->register( 'mc_tag_get_all',
	array(
			'username'		=>	'xsd:string',
			'password'		=>	'xsd:string',
			'page_number'	=>	'xsd:integer',
			'per_page'		=>	'xsd:integer'
	),
	array(
			'return'	=>	'tns:TagDataSearchResult'
	),
	$t_namespace,
	false, false, false,
	'Gets all the tags.'
);

$l_oServer->register( 'mc_tag_add',
	array(
			'username'			=>	'xsd:string',
			'password'			=>	'xsd:string',
			'tag'				=>	'tns:TagData'
	),
	array(
			'return'	=>	'xsd:integer'
	),
	$t_namespace,
	false, false, false,
	'Creates a tag.'
);

$l_oServer->register( 'mc_tag_delete',
	array(
			'username'			=>	'xsd:string',
			'password'			=>	'xsd:string',
			'tag_id'			=>	'xsd:integer'
	),
	array(
			'return'	=>	'xsd:boolean'
	),
	$t_namespace,
	false, false, false,
	'Deletes a tag.'
);

###
###  IMPLEMENTATION
###

/**
 * Checks if the request for the webservice is a documentation request (eg:
 * WSDL) or an actual webservice call.
 *
 * The implementation of this method is based on soap_server::service().
 *
 * @param $p_service    The webservice class instance.
 * @param $p_data       The input that is based on the post data.
 */
function mci_is_webservice_call( $p_service, $p_data )
{
	global $QUERY_STRING;
	global $_SERVER;

	if ( isset( $_SERVER['QUERY_STRING'] ) ) {
		$t_qs = $_SERVER['QUERY_STRING'];
	} else if( isset( $GLOBALS['QUERY_STRING'] ) ) {
		$t_qs = $GLOBALS['QUERY_STRING'];
	} else if( isset( $QUERY_STRING ) && $QUERY_STRING != '' ) {
		$t_qs = $QUERY_STRING;
	}

	if ( isset( $t_qs ) && preg_match( '/wsdl/', $t_qs ) ){
		return false;
	} else if ( $p_data == '' && $p_service->wsdl ) {
		return false;
	} else {
		return true;
	}
}

# pass incoming (posted) data
if ( isset( $HTTP_RAW_POST_DATA ) ) {
	$t_input = $HTTP_RAW_POST_DATA;
} else {
	$t_input = implode( "\r\n", file( 'php://input' ) );
}

# only include the MantisBT / MantisConnect related files, if the current
# request is a webservice call (rather than webservice documentation request,
# eg: WSDL).
if ( mci_is_webservice_call( $l_oServer, $t_input ) ) {
	require_once( 'mc_core.php' );
} else {
	# if we have a documentation request, do some tidy up to prevent lame bot loops e.g. /mantisconnect.php/mc_enum_etas/mc_project_get_versions/
	$parts = explode ( 'mantisconnect.php/', strtolower($_SERVER['SCRIPT_NAME'] ), 2 );
	if (isset( $parts[1] ) && (strlen ( $parts[1] ) > 0 ) ) {
		echo 'This is not a SOAP webservice request, for documentation, see ' .  $parts[0] . 'mantisconnect.php';
		exit();
	}
}

# Execute whatever is requested from the webservice.
$l_oServer->service( $t_input );
