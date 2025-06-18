<h1>List</h1>
<form action="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/<?php echo htmlspecialchars($list)?>/" method="post">
<table class="table" cellpadding="0" cellspacing="0" width="100%" ng-non-bindable>
	<thead>
	<tr>
	    <th width="1%">ID</th>
	    <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('useradmin/list','Status')?></th>		  
	    <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('useradmin/list','Reload')?></th>		  
	</tr>
	</thead>
	<?php foreach ($items as $key => $item) : ?>
    <tr>
        <td><?php echo $key+$pages->low?></td>
        <td class="fs11"><?php echo htmlspecialchars($item)?></td>
        <td class="fs11">
        <?php if ($list == 'resque:failed') : ?>
        <a href="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/resque:failed/(reload)/<?php echo json_decode($item)->payload->class;?>" class="btn btn-default btn-xs"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('useradmin/list','Reload')?></a>
        <?php endif; ?>
        </td>        
    </tr>
	<?php endforeach; ?>
</table>

<?php if (isset($pages)) : ?>
    <?php include(erLhcoreClassDesign::designtpl('lhkernel/paginator.tpl.php')); ?>
<?php endif;?>

<input type="submit" class="btn btn-secondary" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('useradmin/list','Clean queue')?>" name="cleanActions"/>

</form>