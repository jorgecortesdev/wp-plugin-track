<table class="wpp-track-metabox-ctr">
    <thead>
        <tr>
            <th>SID</th>
            <th>PID</th>
            <th>Title</th>
            <th>Graph</th>
            <th>Imp.</th>
            <th>Clicks</th>
            <th>CTR</th>
        </tr>
    </thead>
    <tbody id="tbody-sparkline">
    <?php foreach($posts as $post) : ?>
        <tr>
            <th><?=$post->section_id?></th>
            <td class="text-right"><a target="_blank" href="<?=get_edit_post_link($post->post_id)?>"><?=$post->post_id?></a></td>
            <td><?=$post->post_title?></td>
            <td data-sparkline="<?=implode(', ', $sparklines[$post->section_id][$post->post_id])?>"/>
            <td class="text-right"><?=$post->impressions?></td>
            <td class="text-right"><?=$post->clicks?></td>
            <td class="text-right"><?=number_format($post->ctr * 100, 2)?>%</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>