<thead>
    <tr class="liste_titre malign" width="100%">
        <td width="30" align="center" class="td-file-nr"><?php echo $langs->trans("Nr") ?></td>
        <td class="td-file-name"><?php echo $langs->trans("File") ?></td>
        <td class="td-file-desc"><?php echo $langs->trans("Description") ?></td>
        <td class="td-file-rev"><?php echo $langs->trans("Revision") ?></td>
        <td class="td-file-size"><?php echo $langs->trans("Size") ?></td>
        <td width="110" class="td-file-modif"><?php echo $langs->trans("Modified") ?></td>
        <td class="td-file-user"><?php echo $langs->trans("User") ?></td>
        <?php if ($toolbox) { ?>
            <td class="td-file-toolbox"></td>
        <?php } ?>
    </tr>
</thead>