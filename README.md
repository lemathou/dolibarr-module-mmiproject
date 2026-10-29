# MMIProject — Projets MMI : suivi, temps, rentabilité et facturation.

> Module Dolibarr **MMIProject** (version 1.0.3), branche `20.0`.

## Fonctionnalités

### Fonctionnalités principales

- Suivi de projets, temps et rentabilité.
- Facturation et menus projets.

### Écrans et menus

- **Gestion du temps** — `/custom/mmiproject/index.php` (menu project, droit `$user->rights->mmiproject->time->user`)
- **Récapitilatif mensuel** — `/custom/mmiproject/time_monthly.php` (menu project, droit `$user->rights->mmiproject->time->user && $user->rights->mmiproject->time->show`)
- **Saisie du temps** — `/custom/mmiproject/time_form.php` (menu project, droit `$user->rights->mmiproject->time->user`)
- **Planning de chantiers** — `/custom/mmiproject/chantiers_planning.php` (menu project, droit `$user->rights->mmiproject->time->chantiers`)
- **Contrats de travail** — `/custom/mmiproject/contrat.php` (menu project, droit `$user->rights->mmiproject->time->chantiers`)
- **Statistiques (ouvrages...)** — `/custom/mmiproject/stats.php` (menu project, droit `$user->rights->mmiproject->time->stats`)
- **Catégories de ressources** — `/custom/mmiproject/type_resources.php` (menu tools, droit `$user->rights->mmiproject->resources->cat`)

### Pages

- `admin/about.php`
- `admin/setup.php`
- `ajax.php`
- `chantiers_planning.php`
- `contrat.php`
- `mmiprojectindex.php`
- `product_resources.php`
- `projet_resources.php`
- `stats.php`
- `task_resources.php`
- `task_time_record.php`
- `time_form.php`
- `time_monthly.php`
- `type_resources.php`
- `user_contract.php`

### Permissions

- `mmiproject.time.user` — Use personnal TimeSheet
- `mmiproject.time.admin` — Show everyones TimeSheet
- `mmiproject.time.chantiers` — Show chantiers planning
- `mmiproject.time.stats` — Show stats
- `mmiproject.resources.product` — Link Resources with Products
- `mmiproject.resources.cat` — Edit Resources cat
- `mmiproject.contract.all` — Update user contracts and set hours
- `mmiproject.time.show` — Show TimeSheet

### Configuration

| Constante | Type | Rôle |
|---|---|---|
| `MMIPROJECT_TIME` | separator | Gestion du temps/planning |
| `PROJECT_ADDTIMESPENT_MULTIPLE_USERID` | yesno | Formulaire ajout de temps passé : choix multiple des utilisateurs |
| `MMIPROJECT_SOLIDAY` | date | Journée de solidarité |
| `MMIPROJECT_DECENNALE` | separator | Gestion décénnale |
| `MMIPROJECT_DECENNALE_FIELD` | yesno | Champ afficher le texte décénnale dans Devis / Commande / Facture |
| `MMIPROJECT_DECENNALE_TITLE` | yesno | Préfixer avec un titre 'Décennale' |
| `MMIPROJECT_DECENNALE_TEXT` | textarea | Texte à ajouter pour la décénnale |
| `MMIPROJECT_TASKS` | separator | Gestion des tâches |
| `TASK_SHOW_PARENT_LABEL` | yesno | Afficher le label de la tâche parente dans la tâche |
| `PROJECT_ALLOW_COMMENT_ON_PROJECT` | yesno | Commentaires sur les projets |
| `PROJECT_ALLOW_COMMENT_ON_TASK` | yesno | Commentaires sur les tâches |
| `TASK_CREATE_WITHOUT_DEFAULT_CONTACT` | yesno | Ne pas assigner de contact par défaut à la création d'une tâche |
| `PROJECT_TASK_LIST_LABEL_DISP` | array | Affichage du libellé de la tâche dans les listes |
| `MMIPROJECT_HOLIDAYS` | separator | Gestion des congés |
| `MMIHOLIDAY_ALLOW_REQUEST_WITHOUT_OPEN_DAY` | yesno | Autoriser les demandes de congés payés sur des jours fériés (utile lorsque c'est un jour de solidarité) |

### Hooks

- Contextes : `ordercard`, `tasklist`, `projecttaskcard`, `projecttaskscard`, `projecttasktime`
- Callbacks : `getNextValue`, `formConfirm`, `doPreMassActions`, `doMassActions`, `addMoreMassActions`, `beforePDFCreation`, `afterPDFCreation`, `loadDataForCustomReports`, `restrictedArea`, `completeTabsHead`, `printFieldPreListTitle`, `printFieldListWhere`, `printFieldListFrom`, `printFieldListSearchParam`

### Triggers

- `core/triggers/interface_99_modMMIProject_MMIProjectTriggers.class.php`

### Classes et composants

- `class/actions_mmiproject.class.php`
- `lib/mmiproject.lib.php`

## Prérequis

- Dolibarr >= 11.0 (branche `20.0` : Dolibarr 20.0).
- Modules MMI : `mmicommon`. **`mmicommon` doit être activé en premier.**
- Modules cœur : `projet`.

## Installation

1. Copier le module dans `htdocs/custom/mmiproject` :

   ```sh
   cd htdocs/custom
   git clone <dépôt> mmiproject
   ```

2. Dans Dolibarr : **Configuration → Modules**, activer d'abord `mmicommon`, puis ce module.
3. Vérifier les permissions dans la fiche des utilisateurs concernés.

## Licence

GPLv3 ou ultérieure — voir `COPYING`. Documentation sous GFDL.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) (évolutions datées).
