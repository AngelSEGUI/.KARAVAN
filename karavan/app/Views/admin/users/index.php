<?php
/**
 * @var array $users
 * @var string $q
 * @var string $filter
 * @var int $me
 */
?>
<header class="admin-head">
    <div>
        <span class="eyebrow">Communauté</span>
        <h1>Membres <span class="head-count"><?= count($users) ?></span></h1>
    </div>
</header>

<form class="toolbar" method="get" action="<?= url('/admin/membres') ?>" data-autosubmit>
    <label class="field field-search">
        <span class="sr-only">Rechercher</span>
        <svg class="icon"><use href="#i-search"/></svg>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, email, ville…">
    </label>
    <div class="segmented" role="group" aria-label="Filtrer">
        <?php foreach (['' => 'Tous', 'admin' => 'Admins', 'suspended' => 'Suspendus'] as $k => $label): ?>
            <a href="<?= url('/admin/membres', ['q' => $q, 'filtre' => $k]) ?>" class="<?= $filter === $k ? 'is-active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Membre</th>
                <th>Inscription</th>
                <th>Activité</th>
                <th>Statut</th>
                <th class="col-actions"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u):
            $isMe = (int) $u['id'] === $me;
            $isAdmin = $u['role'] === 'admin';
            $suspended = (int) $u['suspended'] === 1; ?>
            <tr class="<?= $suspended ? 'is-muted' : '' ?>">
                <td data-label="Membre">
                    <div class="cell-main">
                        <span class="avatar avatar-sm"><?= e(initials($u['name'])) ?></span>
                        <div>
                            <span class="cell-title"><?= e($u['name']) ?><?= $isMe ? ' <span class="muted">(vous)</span>' : '' ?></span>
                            <small class="muted"><?= e($u['email']) ?><?= $u['city'] ? ' · ' . e($u['city']) : '' ?></small>
                        </div>
                    </div>
                </td>
                <td data-label="Inscription"><?= e(date_fr((string) $u['created_at'])) ?></td>
                <td data-label="Activité">
                    <span class="offer-counts">
                        <span title="Trajets proposés"><svg class="icon icon-sm"><use href="#i-car"/></svg><?= (int) $u['rides_count'] ?></span>
                        <span title="Logements proposés"><svg class="icon icon-sm"><use href="#i-home"/></svg><?= (int) $u['housings_count'] ?></span>
                        <span title="Réservations"><svg class="icon icon-sm"><use href="#i-ticket"/></svg><?= (int) $u['bookings_count'] ?></span>
                    </span>
                </td>
                <td data-label="Statut">
                    <?php if ($isAdmin): ?><span class="pill pill-admin">Admin</span><?php endif; ?>
                    <?php if ($suspended): ?><span class="pill pill-warn">Suspendu</span><?php endif; ?>
                    <?php if (!$isAdmin && !$suspended): ?><span class="pill">Membre</span><?php endif; ?>
                </td>
                <td class="col-actions">
                    <?php if (!$isMe): ?>
                        <form method="post" action="<?= url('/admin/membres/' . $u['id'] . '/role') ?>" class="inline-form"
                              data-confirm="<?= e($isAdmin ? "Retirer les droits d'administration à {$u['name']} ?" : "Donner les droits d'administration à {$u['name']} ?") ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="icon-btn <?= $isAdmin ? 'is-on' : '' ?>" title="<?= $isAdmin ? 'Retirer admin' : 'Nommer admin' ?>"><svg class="icon"><use href="#i-shield"/></svg></button>
                        </form>
                        <form method="post" action="<?= url('/admin/membres/' . $u['id'] . '/suspension') ?>" class="inline-form"
                              <?= $suspended ? '' : 'data-confirm="' . e("Suspendre {$u['name']} ? Il ne pourra plus se connecter.") . '"' ?>>
                            <?= csrf_field() ?>
                            <button type="submit" class="icon-btn <?= $suspended ? 'is-warn' : '' ?>" title="<?= $suspended ? 'Réactiver' : 'Suspendre' ?>"><svg class="icon"><use href="#i-ban"/></svg></button>
                        </form>
                        <?= partial('admin-delete', [
                            'action'  => '/admin/membres/' . $u['id'] . '/supprimer',
                            'confirm' => "Supprimer définitivement le compte de {$u['name']} ? Ses trajets, logements et réservations seront supprimés.",
                        ]) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!$users): ?><p class="table-empty">Aucun membre trouvé.</p><?php endif; ?>
</div>
