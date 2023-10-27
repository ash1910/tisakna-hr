<pre style="  white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap;  white-space: -o-pre-wrap; word-wrap: break-word;">
=====================
EXAMPLES
=====================

{exp:data_import:start import="list_name1|list_name2|list_name_3"}{/exp:data_import:start}

{exp:data_import:start import="{segment_3}"}{/exp:data_import:start}

Add this to your template to run the import.

=====================
SETTINGS
=====================

Once installed, add your remote database connection information to the Remote DB Settings tab - this could be localhost / external connection.

You can then create a new Import from the Import List tab.

You will now see your database tables populated in a select field:

	- Select the table you wish to import from
	- If you need to join multiple tables to collate data, click the join link - the DataImport module will attempt to locate a common key (eg entry_id) - You can override this setting by manually changing the text field
	- Select the channel you wish to import to. This will auto generate the fieldtypes set for this channel and allow you to select which column corresponds to which field. It is advisable to setup the required fields / matrixes before running the import
	- ‘Key’ - tells the module what field to use to check for duplicates. So every row will match by the key field. Eg: remote.sku=local.sku.
	- ‘Required’ - will not create record / entry if field is empty / null.
	- ‘Condition’ - will not create record / entry if condition is not met.

Finally, once all your fields are mapped, you can submit the import to save it. 

</pre>