<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/admin/workflow.php
 *	\ingroup    company
 *	\brief      Workflows setup page
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

$langs->load("admin");
$langs->load("workflow");

if (! $user->admin) accessforbidden();

$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set(.*)/',$action,$reg))
{
    if (! dolibarr_set_const($db, $reg[1], 1, 'chaine', 0, '', $conf->entity) > 0)
    {
        dol_print_error($db);
    }
}

if (preg_match('/del(.*)/',$action,$reg))
{
    if (! dolibarr_del_const($db, $reg[1], $conf->entity) > 0)
    {
        dol_print_error($db);
    }
}


/*
 * 	View
 */

llxHeader('',$langs->trans("WorkflowSetup"),'');

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("WorkflowSetup"),$linkback,'setup');

print $langs->trans("WorkflowDesc").'<br>';
print "<br>";

// List of workflow we can enable

print '<table class="noborder" width="100%">'."\n";
print '<tr class="liste_titre">'."\n";
print '  <td>'.$langs->trans("Description").'</td>';
print '  <td align="center">'.$langs->trans("Status").'</td>';
print "</tr>\n";

clearstatcache();

$workflowcodes=array(
	'WORKFLOW_PROPAL_AUTOCREATE_ORDER'=>array('enabled'=>($conf->propal->enabled && $conf->commande->enabled),'child'=>'order','parent'=>'propal'),
	'WORKFLOW_ORDER_CLASSIFY_BILLED_PROPAL'=>array('enabled'=>($conf->propal->enabled && $conf->commande->enabled),'child'=>'propal','parent'=>'order'),
	'WORKFLOW_ORDER_AUTOCREATE_INVOICE'=>array('enabled'=>($conf->commande->enabled && $conf->facture->enabled),'child'=>'invoice','parent'=>'order'),
	'WORKFLOW_INVOICE_CLASSIFY_BILLED_ORDER'=>array('enabled'=>($conf->facture->enabled && $conf->commande->enabled),'child'=>'order','parent'=>'invoice'),
);

if (! empty($conf->modules_parts['workflow']) && is_array($conf->modules_parts['workflow'])) $workflow = array_merge($workflow, $conf->modules_parts['workflow']);

$nbqualified=0;

foreach($workflowcodes as $key => $tmparray)
{
	$child=$tmparray['child'];
	$parent=$tmparray['parent'];
   	if (empty($conf->$child->enabled) || empty($conf->$parent->enabled)) continue;

   	$nbqualified++;
	$var = !$var;
   	print "<tr ".$bc[$var].">\n";
   	print "<td>".img_object('', $tmparray['child']).$langs->trans('desc'.$key)."</td>\n";
   	print '<td align="center">';
   	if ($conf->use_javascript_ajax)
   	{
   		print ajax_constantonoff($key);
   	}
   	else
   	{
   		if (! empty($conf->global->$key))
   		{
   			print '<a href="'.$_SERVER['PHP_SELF'].'?action=del'.$key.'">';
  			print img_picto($langs->trans("Activated"),'switch_on');
   			print '</a>';
   		}
   		else
   		{
   			print '<a href="'.$_SERVER['PHP_SELF'].'?action=set'.$key.'">';
  			print img_picto($langs->trans("Disabled"),'switch_off');
   			print '</a>';
   		}
   	}
   	print '</td>';
   	print '</tr>';
}

if ($nbqualified == 0)
{
    print '<tr><td colspan="3">'.$langs->trans("ThereIsNoWorkflowToModify");
}
print '</table>';


llxFooter();

$db->close();
?>
