# Journal complet — refonte casaverde-tienda.com et casa-bazar.com

Période : du 2026-10-26 au 2026-10-28. Tout le travail a été fait **en local** (Laravel Herd, domaines `casaverde.test` et `casabazar.test`). **Rien n'a été envoyé ni importé en production.**

Ce document récapitule, dans l'ordre, chaque demande, ce qui a été fait, les fichiers produits, les vérifications, les erreurs rencontrées et leur correction, puis ce qui reste à faire. Les rapports détaillés restent dans [INTEGRATION.md](INTEGRATION.md), [MANQUANTS.md](MANQUANTS.md), [RISQUES.md](RISQUES.md) et `inventaire/`.

---

## Sommaire

1. Cadre et règles de travail
2. Environnement local
3. Inventaire des deux boutiques (phase 0)
4. Migration des contenus et redirections 301
5. Design : deux identités distinctes
6. Intégration WordPress des maquettes validées
7. Comparaison avec casapergolasolar.com, checkout et moyens de paiement dynamiques
8. Fiches produit, icônes, favoris et comparateur, vente tous pays, checkout simplifié
9. Homes, virement avec justificatif, relances, annulation, e-mails-documents, favicons
10. Incident : anciennes commandes traitées par la tâche automatique
11. FAQ, CGV, en-tête responsive et archives .wpress
12. Recherche de l'extension « fichiers lourds »
13. Inventaire des fichiers, scripts et versions
14. Erreurs rencontrées et corrections
15. Ce qui n'a pas été fait
16. À faire par le client avant la mise en ligne
17. Liste des demandes, dans l'ordre

---

## 1. Cadre et règles de travail

Le brief (refonte, phases 0 à 8, points d'arrêt) imposait :

- **Ne rien inventer** : avis, notes, nombre de clients, délais, frais, certifications DINplus/ENplus non prouvées, adresse, NIF.
- **Aucune fausse urgence.**
- **Aucun prix barré** sans historique prouvant le prix le plus bas des 30 derniers jours (directive Omnibus).
- **Aucune catégorie vide**, aucun lien vers une page vide, aucun lien `#`.
- **Aucun changement d'URL** sans redirection 301 vers la page la plus proche.
- **Toute information manquante** va dans `MANQUANTS.md` et n'est jamais comblée.
- **« Rien ne part en production sans validation écrite de ma part. »**

Consignes données ensuite :
- Installer MariaDB portable hors Herd, puis continuer.
- « Fais tout pour réussir les tâches toi-même… tu dois finir ça. »
- « Je te laisse champ libre… prends les autorisations toi-même. »
- Travailler en autonomie, sans le navigateur intégré, que vous aviez refusé.

Ces règles ont été appliquées à chaque étape. Les cas limites sont signalés dans les sections concernées.

---

## 2. Environnement local

| Élément | Détail |
|---|---|
| Serveur web | Laravel Herd (Windows), PHP 8.4 (`C:\Users\LENOVO\.config\herd\bin\php84\php.exe`) |
| Base de données | **MariaDB 11.4.9 portable**, hors Herd, port **3310**, root sans mot de passe en local, utilisateur `casa` / `casa_local`. Dossiers `_tools\mariadb\` et `_tools\mariadb-data\`, démarrage `_tools\start-db.bat` |
| WP-CLI | `wp-cli.phar` téléchargé, lancé par `_tools\wp.bat` |
| Sauvegardes d'origine | `.wpress` All-in-One WP Migration (casaverde 751 Mo, casa-bazar 592 Mo), copiées intactes dans `_tools\backups-originaux\` |
| Extraction | `wpress-extract` (npm) vers `_tools\extracted\` ; `SERVMASK_PREFIX_` remplacé par le vrai préfixe : `wp_` (Casa Verde), `wpzf_` (Casa Bazar) |
| Outils de contrôle | puppeteer-core + Edge sans interface (captures, émulation mobile, commandes de test, débordements) ; détecteur Impeccable ; `scripts/diff-check.mjs` |
| Sites locaux | `C:\Users\LENOVO\Herd\casaverde` (WooCommerce 10.2) et `C:\Users\LENOVO\Herd\casabazar` (WooCommerce 11.2) ; WordPress 7.1.3 |

---

## 3. Inventaire des deux boutiques (phase 0)

Rapports : `inventaire/casaverde.md`, `inventaire/casabazar.md`, données brutes dans `inventaire/raw/` (HTML de home et de fiche, codes HTTP, JSON).

**Casa Verde** (es_ES, CASA VERDE 42 SL, CIF B38339198, El Sauzal, Tenerife)
- 14 produits, titres et slugs majoritairement en **portugais**, base `/produto`.
- Tout le catalogue rangé dans une seule catégorie, « Pellets de madera ».
- Catégorie « Accesorios de chimenea » vide.
- Chaînes de thème en anglais ; bloc promo en portugais.
- Viewport `user-scalable=no`.
- Prix « IVA incluido » alors que `prices_include_tax = no` ; question IVA / IGIC (Canaries).
- Promesses de la home (« envío gratuito », « 3-5 días », « devolución gratuita ») contredites par les CGV (6 à 13 jours ouvrés).
- Un seul moyen de paiement réel (virement), alors que les CGV annonçaient carte et PayPal.
- Trois administrateurs, dont deux inconnus : `Agent` et `Staff`, avec des adresses Gmail.
- Page `/sobre-silva-lenha-verde/` au nom d'une autre enseigne.

**Casa Bazar** (pt_PT, ELECTRO BAZAR, NIF 151485208, Santarém)
- 39 produits : 24 lots de pellets et 15 barbecues, fours et accessoires.
- « Certificado ENplus » dans les 24 titres, sans aucune preuve.
- 24 prix barrés sans historique.
- Catégorie vide « Acessórios » ; catégorie « Barbacoa » en espagnol.
- Pages « Wishlist » et « Compare » non traduites ; `calc_taxes = no` ; aucune passerelle active.
- Objet social déclaré (nettoyage, articles ménagers) à vérifier face au catalogue.

Synthèse des risques : `RISQUES.md` R1 à R8, dont un **risque Merchant Center** si les deux boutiques sont rattachées (catalogues et SKU `00B…` proches, même pile technique).

---

## 4. Migration des contenus et redirections 301

Scripts `_tools\mig-casaverde.php` et `_tools\mig-casabazar.php`, idempotents.

- **Casa Verde** : titres produits en espagnol ; slugs espagnols ; catégories par type (Pellets, Briquetas, Leña de roble, Encendedores) ; base `/producto/`. Pages « Sobre » et « FAQ » sorties du sous-menu « Aviso Legal ». Pied de page fautif corrigé.
- **Casa Bazar** :
  - « Certificado ENplus » retiré des 24 titres.
  - Prix barrés supprimés : le prix payé reste le prix actuel.
  - Catégorie « Barbacoa » → « Barbecues » ; titres des accessoires Weber traduits.
  - Slugs anglais corrigés.
- **Redirections 301** : fichier `wp-content/redirects-map.json` (23 entrées pour Casa Verde, 46 pour Casa Bazar), servi par les mu-plugins `casaverde-core.php` et `casabazar-core.php`. Casa Verde redirige aussi tout `/produto/…` vers `/producto/…` en cas de 404.
- **Pages légales** :
  - Page de **rétractation** avec bouton en ligne : `/desistimiento/` (`[boton_desistimiento]`) et `/livre-resolucao/` (`[livre_resolucao]`), obligatoire depuis le 19/06/2026.
  - Pages « Política de cookies » créées.
- **Sauvegardes** : `_tools\dumps\<site>-apres-import-local.sql` pour l'état d'origine ; textes d'origine conservés.

Données manquantes consignées dans `MANQUANTS.md` (C1 à C10, V1 à V8, B1 à B8) : GTIN, poids, barème de transport, passerelles réelles, SMTP, comptes Google, registre mercantil, etc.

---

## 5. Design : deux identités distinctes

Brief design : deux directions strictes, **A Casa Verde « Hogar cálido »** et **B Casa Bazar « Ficha técnica »**.
- Listes de tokens et home en 6 blocs.
- Affichage du **€/kg obligatoire** (directive 98/6/CE).
- Processus Impeccable, script `diff-check`, page `compare.html`, un `DESIGN.md` par site, un CSV des images.

Vos réponses à mes questions :
- Casa Bazar entièrement en portugais, comparateur €/kg limité aux pellets.
- Héros Casa Verde avec les 3 €/kg les plus bas, jamais « más vendidos » (aucune vente enregistrée).

Livrables dans `design/` :
- `casaverde/` et `casabazar/` contiennent pour chaque site :
  - `PRODUCT.md` et `DESIGN.md` ;
  - `data.mjs`, `build.mjs`, `products.raw.json`, `wc-data.json`, `elementor-home.json` ;
  - `images-a-traiter.csv` ;
  - `maquette/` : home, catégorie, fiche, panier.
- `compare.html` : les deux directions côte à côte. `shots/` : captures.
- **Tokens A** : salvia `#A9B08E`, crema `#F1EBE1`, corteza `#3A2A20`, musgo `#4F5B3A`, blanco `#FFFFFF`. Bricolage Grotesque + Figtree. Rayons 28 px et 999 px, aucune ombre.
- **Tokens B** : papel `#F4F3EF`, carbón `#161616`, grafito `#5E5E59`, línea `#D9D7D0`, brasa `#C4471A`, foto `#EAE8E2`. Archivo + IBM Plex Mono. Angles droits.
- `scripts/diff-check.mjs` vérifie qu'aucune couleur, police ni rayon n'est partagé entre les deux sites.
- Contrastes vérifiés. Seules exceptions, imposées par le brief A : le fond crema et le wordmark crema sur salvia (logotype `aria-hidden`).
- Poids manquant ou incohérent : 5 produits Casa Verde affichent « Precio por kg pendiente de confirmar el peso » (voir MANQUANTS).

**Point d'arrêt** : vous avez validé les deux directions (« Je valide les deux directions. Passe à l'intégration WordPress à partir des DESIGN.md »).

---

## 6. Intégration WordPress des maquettes validées

- **Thèmes sur mesure** `themes/casaverde` et `themes/casabazar` (v2.0.0), sans Woodmart, versionnés avec git. Commits `1a2efa8` / `ae0e1c7`.
- **Styles** :
  - `assets/a.css` et `assets/b.css` reprennent le CSS des maquettes, avec une couche WooCommerce et Elementor.
  - Polices auto-hébergées et préchargées ; Google Fonts d'Elementor désactivé.
- **Home en sections Elementor** (conteneurs avec `css_classes`), construites par `_tools\elementor-home.php` à partir de `elementor-home.json`. Les blocs sont des shortcodes à attributs, modifiables dans Elementor :
  - Casa Verde : `[cv_heroe]`, `[cv_categorias]`, `[cv_seleccion]`, `[cv_condiciones]`, plus un bloc « Quiénes somos » en widgets natifs.
  - Casa Bazar : `[cb_heroe]`, `[cb_comparativo]`, `[cb_categorias]`, `[cb_seleccion]`, `[cb_condicoes]`.
- **Global Colors / Global Fonts** Elementor écrits dans le kit de chaque site (kit 6 pour Casa Verde, kit 7 pour Casa Bazar).
- **€/kg** calculé depuis le champ poids WooCommerce, renseigné pour 9 produits sur 14 (Casa Verde) et 24 lots sur 24 (Casa Bazar). Métas `_casa_short`, `_casa_specs`, `_casa_key` / `_casa_lines`, `_casa_kgSaco`, `_casa_sacosPal`, `_casa_humidade`.
- **Gabarits** :
  - catégorie : grille, filtres Tipo / Peso del saco (Casa Verde), Marca / Paletes en vue liste ou grille (Casa Bazar), tri par €/kg ;
  - fiche produit avec ficha técnica ;
  - panier avec €/kg par ligne.
- **Contenus nettoyés** :
  - paragraphes « Entrega » contraires à la page de livraison retirés des 24 lots ;
  - mentions de certification non prouvées retirées (1817, 1821, 1838, 1841) ;
  - niveaux de titres renumérotés à l'affichage (`casa_fix_headings`).
- **Sécurité** : XML-RPC coupé ; en-têtes nosniff / SAMEORIGIN / Referrer-Policy / Permissions-Policy ; pages auteur redirigées ; `/wp/v2/users` masqué ; `DISALLOW_FILE_EDIT`.
- **Performance** : CSS et JS WooCommerce, blocs, Brands et CF7 chargés seulement là où ils servent.
- **Prix** au format `305,00 €`.
- **Vérifications** :
  - codes 200 sur les pages clés ;
  - détecteur Impeccable à 0 (hors exceptions du brief) ; `diff-check` OK ;
  - ajout au panier, panier et commande OK ;
  - 28 pages chargées comme documents Elementor sans erreur ;
  - aucun débordement à 375 px.

---

## 7. Comparaison avec casapergolasolar.com, checkout et moyens de paiement dynamiques

Demande : analyser casapergolasolar.com, combler ce qui manque, soigner surtout le dernier bouton du checkout, et faire en sorte qu'un moyen de paiement activé soit cité automatiquement partout.

Le site en ligne ne répondait pas depuis la machine ; la comparaison porte sur sa copie locale (`Projets\cps`, thème `casapergola`).

- **Bouton final légal** : « Confirmar pedido con obligación de pago » (art. 98.2 TRLGDCU) / « Encomenda com obrigação de pagar » (DL 24/2014).
- **Moyens de paiement calculés depuis les passerelles actives** (`inc/payments.php`, fonctions `cv_payment_*` / `cb_payment_*`) :
  - ils apparaissent dans le pied de page, la bande « conditions », le panier, la note avant le bouton, les CGV et la FAQ ;
  - dans les pages, ils passent par les shortcodes `[metodos_pago]` / `[meios_pagamento]` et le texte conditionnel `[si_pago]` / `[se_pagamento]` ;
  - **testé** : activer puis désactiver « contra reembolso » met toutes ces zones à jour.
  - Les CGV et la FAQ de Casa Verde annonçaient carte et PayPal, qui n'existent pas : remplacés par les shortcodes.
- **Livraison** : zones « España Peninsular » (hors PM, GC, TF, CE, ML) et « Portugal continental » (CP 1000-000 à 8999-999), livraison gratuite ; ligne « Entrega » au panier ; message hors zone.
- **CGV** liées à la case du checkout sur les deux sites (Casa Bazar n'en avait aucune).
- **Bandeau cookies** AEPD / CNPD avec Google Consent Mode v2 (`inc/consent.php`) :
  - boutons Rechazar / Configurar / Aceptar de même poids ;
  - lien « Configurar cookies » dans le pied ;
  - page « Política de cookies » créée.
- **Omnibus** : historique daté des prix (`_casa_price_history`) ; un prix barré ne s'affiche qu'avec 30 jours d'historique prouvant le prix de référence.
- **`noindex`** sur panier, commande, compte et recherche.
- **E-mails** aux couleurs des tokens, identité légale en pied (+ Livro de Reclamações au Portugal), nom d'expéditeur (`_tools\emails.php`).
- **Passerelles** : virement Casa Verde renommé « Transferencia bancaria » avec une nouvelle description. Virement Casa Bazar activé, mais **sans IBAN** (C15).
- Commits `31383d4` / `6095c77`. Commandes de test 2033 (Casa Verde) et 1464 (Casa Bazar), mises à la corbeille.

---

## 8. Fiches produit, icônes, favoris et comparateur, vente tous pays, checkout simplifié

Demande : textes « cloîtrés » sur les fiches, icônes panier / wishlist / comparer / compte selon les règles GMC et pas seulement en texte, paiement depuis n'importe quel pays avec livraison gratuite seulement dans le pays d'origine, checkout facilité.

- **Fiches produit** :
  - description en deux colonnes, titre à gauche et texte à mesure lisible à droite (`.desc` + `.desc__body`) ;
  - Casa Bazar : les tableaux copiés d'un revendeur, qui se cassaient lettre par lettre, sont regroupés à l'affichage en un seul tableau caractéristique / valeur (`cb_flatten_tables`), et la référence interne « MRKEAN » est masquée ;
  - restes de balises retirés de 3 fiches ; titre « Barbecue WEBER Traveler portátil » corrigé (1294).
- **Icônes SVG** compte, favoris, comparer et panier, propres à chaque identité (trait arrondi / trait droit) :
  - chaque icône a un nom accessible ;
  - le libellé du panier est visible en desktop ;
  - les compteurs se mettent à jour en direct.
- **Favoris et comparateur fonctionnels** (gardés dans le navigateur : `cv_wish` / `cv_cmp`, `cb_wish` / `cb_cmp`) :
  - boutons sur les cartes, les lignes de liste et les fiches, avec `aria-pressed` et un message de confirmation ;
  - pages `/lista-de-deseos/` (2034) et `/comparar/` (2035) pour Casa Verde, `/lista-de-desejos/` (1465) et `/comparar/` (1466) pour Casa Bazar ;
  - 4 produits au maximum dans le comparateur ; anciennes URL redirigées.
- **Pays** :
  - vente ouverte à tous les pays ;
  - livraison gratuite seulement en España Peninsular et au Portugal continental ;
  - ailleurs, îles comprises, méthode « transporte con presupuesto previo al pago » / « transporte com orçamento antes do pagamento » à 0 €, puisque le virement est payé après la commande ;
  - pages « Envío y entrega » et « Sobre nosotros » de Casa Verde mises en cohérence.
- **Checkout simplifié** :
  - ordre des champs : e-mail, nom, téléphone (avec explication), pays, adresse, code postal, ville, le même pour tous les pays ;
  - société et complément d'adresse retirés ;
  - champ coupon masqué tant qu'aucun code n'existe ;
  - pas d'invite de connexion, achat sans compte ;
  - récapitulatif fixe à droite en desktop.
- **Tests** :
  - 4 commandes (ES et FR sur Casa Verde, PT et FR sur Casa Bazar), méthodes de livraison correctes, mises à la corbeille ;
  - détecteur Impeccable à 0 ; aucun débordement mobile ; `diff-check` OK.
- **Merchant Center** : ne déclarer que le pays d'origine tant qu'aucun tarif réel n'existe (C18).
- Commits `6018072` / `7835803`.

---

## 9. Homes, virement avec justificatif, relances, annulation, e-mails-documents, favicons

Demande :
- retirer le comparatif de pellets de la home Casa Bazar et ajouter des sections produits sur les deux homes ;
- demander le justificatif de virement sur la page de remerciement (extensions limitées, envoi par e-mail, passage « en cours ») ;
- envoyer toutes les 24 h un e-mail de relance avec les coordonnées bancaires à jour, et annuler à 72 h ;
- passer automatiquement « en cours » → « terminée » selon le délai maximal ;
- rendre les e-mails professionnels (proforma, bon de livraison) et changer les favicons.

### Homes
- **Casa Bazar** : comparatif retiré. Les liens « Comparar com outros pellets » de la fiche et « Comparativo de pellets » du pied renvoient vers la catégorie pellets, qui affiche le €/kg. Le JS de tri du comparatif est supprimé.
  - Sections : Seleção, Pellets (lots de 1 palete), Barbecues, Fornos de pizza, Acessórios para barbecue.
- **Casa Verde** : sections Selección, Pellets de madera (les 8), Briquetas de madera, Leña de roble y encendedores.
- Les shortcodes `[cb_seleccion categoria= limite= ligacao=]` / `[cv_seleccion categorias= limite= columnas=3]` gèrent ces sections. Chaque titre a un identifiant unique, et une section vide n'est pas affichée.
- **Lien `#` supprimé** : « Configurar cookies » est devenu un bouton (`.as-link`) sur les deux sites.

### Module commandes (mu-plugin identique sur les deux sites)
Fichiers : `mu-plugins/casa-orders.php` (moteur), `mu-plugins/casa-orders/` (classes d'e-mails, gabarits, `email.css`), `mu-plugins/casa-orders-site.php` (textes es/pt, identité légale, couleurs, délai).

- **Justificatif** sur la page de confirmation et dans « Mon compte », dès que le virement est choisi ou sélectionné seul :
  - formats acceptés : PDF, JPG, PNG, 5 Mo au maximum ;
  - le type réel du fichier est vérifié : un faux PDF est refusé ;
  - fichier stocké hors médiathèque (`wp-content/casa-justificantes/`, nom aléatoire, accès bloqué) ;
  - il est joint à l'e-mail envoyé à la boutique, renommé `justificante-pedido-N` / `comprovativo-encomenda-N` ;
  - il est téléchargeable depuis la commande (encadré « Justificante ») ;
  - la commande passe **en cours**.
  - Accès sécurisé : il reprend la vérification d'e-mail de WooCommerce, mémorisée en session.
- **Cycle automatique** (Action Scheduler, toutes les heures, groupe `casa`) :
  - **24 h** : relance ;
  - **48 h** : dernier avis ;
  - **72 h** : annulation, avec l'e-mail d'annulation au client et à la boutique.
  - Chaque relance contient les **coordonnées bancaires lues au moment de l'envoi**.
  - Si les coordonnées changent dans WooCommerce, chaque commande en attente reçoit aussitôt un e-mail « nouvelles coordonnées », avec un avertissement visible.
- **Clôture automatique** « en cours » → « terminée » après le délai maximal **publié** sur les pages de livraison, en jours ouvrés :
  - Casa Verde : **13 jours** (« 6 a 13 días laborables ») ;
  - Casa Bazar : **8 jours** (expédition en 3 à 4 jours + livraison en 3 à 4 jours).
  - Le réglage `days` se trouve dans `casa-orders-site.php`.
- **Date de mise en service** `casa_orders_since` (27/10/2026) : l'historique n'est jamais touché (voir section 10).
- Note avant le bouton de commande mise à jour : justificatif à envoyer, annulation sans paiement en 72 h.
- Instructions et description du virement réécrites : justificatif à envoyer en ligne ou par e-mail (`_tools\bacs-texts.php`).

### E-mails
- Chaque e-mail de commande contient un **document**. Les e-mails client principaux (en attente, en cours, terminée, annulée) ont une introduction propre au site, au vouvoiement, avec les délais publiés.

| Statut | Document | Numéro |
|---|---|---|
| en attente de paiement | Factura proforma / Fatura pró-forma | PF-n |
| en cours | Confirmación de pedido / Confirmação de encomenda | n |
| terminée | Albarán de entrega / Nota de entrega | ALB-n / NE-n |
| annulée | Pedido cancelado / Encomenda cancelada | n |

- Contenu de chaque document :
  - le vendeur (raison sociale, CIF ou NIF, adresse, contact) et le client (adresses de facturation et de livraison) ;
  - pour chaque ligne : la référence, le poids et le €/kg ;
  - les totaux, le moyen de paiement et le statut ;
  - la mention « sans validité fiscale » propre au type de document, et le droit de rétractation.
  - Pour une commande non payée : bloc de coordonnées bancaires, bouton « Enviar el justificante » et date limite.
- **Deux e-mails ajoutés**, réglables dans WooCommerce › Réglages › E-mails :
  - relance justificatif (24 h, 48 h et nouvelles coordonnées), au client ;
  - justificatif reçu, à la boutique, avec la pièce jointe et une réponse directe possible au client.
- **Réglages** (`_tools\brand-mail-setup.php`) :
  - 12 e-mails WooCommerce activés avec un objet et un titre dans la langue du site ;
  - l'annulation client, désactivée par défaut, est activée ;
  - les e-mails boutique vont à contacto@ / info@ ;
  - logo d'en-tête aux couleurs de chaque marque.
- **Capture locale** `mu-plugins/casa-mail-local.php` : sur *.test, les e-mails sont écrits dans `wp-content/mail-log/` au lieu de partir. Rien n'a été envoyé à quiconque.

### Favicons
- Casa Verde : « cv » corteza sur salvia, coins arrondis. Casa Bazar : « cb » papel sur carbón, avec un repère brasa.
- Générés avec les polices des thèmes (`_tools\brand-assets.mjs`, sources dans `design/brand/`), installés comme icône du site en 32, 180, 192 et 270 px.
- Anciennes icônes conservées dans la médiathèque.

### Tests
- **Justificatif** : commande, puis faux PDF refusé, puis vrai PDF accepté. L'e-mail boutique part avec sa pièce jointe et la commande passe « en cours ».
- **Cycle simulé** : rien à 23 h, relance à 25 h, rien à 26 h, dernier avis à 49 h, annulation à 73 h.
- **Clôture** : à 13 jours ouvrés (Casa Verde) et à 8 (Casa Bazar).
- **« Nouvelles coordonnées »** vérifié.
- **Rendu des e-mails** contrôlé à 720 et 375 px ; deux corrections faites :
  - le texte du virement demandait encore un envoi par e-mail ;
  - « suba aquí » apparaissait dans l'e-mail.
- **Pages** : homes contrôlées en desktop et mobile ; détecteur Impeccable sans nouvelle remontée (mots isolés corrigés par `text-wrap:pretty`) ; aucun lien `#` ; `diff-check` OK.
- Commandes de test (2042, 2043, 2044, 1475, 1476) mises à la corbeille ; fichiers de justificatif de test supprimés.
- Commits thèmes `a31106f` / `2119b38` ; mu-plugins versionnés `9ee89e5` / `0dfbfe1` ; dumps `_tools\dumps\<site>-commandes-2026-10-27.sql`.

---

## 10. Incident : anciennes commandes traitées par la tâche automatique

À sa première exécution, la tâche horaire a traité deux **anciennes commandes réelles** importées de Casa Verde :
- la **1755** a été annulée ;
- la **1754** est passée « terminée ».

- **Aucun e-mail n'a quitté la machine** : ils ont été interceptés par la capture locale.
- **Aucun impact sur le stock** : les produits ne gèrent pas de stock.
- **Corrigé** : statuts rétablis (1755 en attente, 1754 en cours), notes et métas retirées, e-mails capturés effacés (`_tools\casa-revert.php`).
- **Prévention** : option `casa_orders_since`. Le module ne traite que les commandes créées, ou passées en cours, après cette date. Casa Bazar n'a pas été touchée : vérifié par `_tools\casa-affected.php`.

---

## 11. FAQ, CGV, en-tête responsive et archives .wpress

Demande : corriger la FAQ (délais) et les CGV (annulation à 72 h), préparer les fichiers .wpress sans erreur. En cours de route : refaire l'en-tête responsive.

### FAQ et CGV
- **FAQ Casa Verde** (page 1649) : la réponse « ¿Cuánto tarda…? » mêlait deux délais. Elle ne garde plus que celui de la page « Envío y entrega » : 1 à 3 jours de préparation, 6 à 13 jours ouvrés au total.
- **CGV** : clause sur les relances à 24 h et 48 h, puis l'annulation automatique à 72 h sans paiement ni justificatif, sans frais pour le client.
  - Casa Verde : § 4 « Métodos de pago » (page 1686).
  - Casa Bazar : nouvelle section « Encomendas e Pagamento » (page 405, dans Elementor, insérée par `_tools\elementor-insert.php`).
  - La clause n'apparaît que si le virement est actif.
- Scripts `_tools\legal-72h.php`, `page-grep.php`, `page-outline.php`.

### En-tête responsive
- Jusqu'à 900 px (Casa Verde) et 980 px (Casa Bazar) :
  - icône de menu à gauche, **jamais le mot « Menu »** ;
  - logo centré ;
  - **panier seul** à droite.
- Le menu contient la navigation, puis Compte, Favoris et Comparer avec leurs compteurs. Il se ferme au clic à l'extérieur et avec Échap.
- Icône « menu » ajoutée à Casa Bazar ; ancien CSS du menu mobile retiré.
- Testé à 375, 768, 1024 et 1440 px (`_tools\header-test.mjs`) : aucun texte dans la barre, aucun débordement.
- Un défaut corrigé : le compteur des favoris sortait de sa ligne dans le menu Casa Verde.
- Commits `f5dbee6` / `bfc43a5`.

### Archives .wpress (`livrables/`)

| Archive | Taille | SHA-256 (début) | Extensions actives après import |
|---|---|---|---|
| `casaverde-tienda-2026-10-27.wpress` | 720,5 Mo | `EEEC809F57B95AA2` | celles de la production, sans Woodmart Core (17) |
| `casa-bazar-2026-10-27.wpress` | 568,2 Mo | `725FF9D1C13CAE8E` | celles de la production, sans Woodmart Core ni CookieAdmin (12) |

- **All-in-One WP Migration 7.113** installé depuis wordpress.org sur les deux sites locaux. L'export a été lancé entièrement en ligne de commande (`_tools\ai1wm-export.php`).
- **Extensions** (`_tools\plugins-set.php`) :
  - la base locale n'en avait que 4 ou 5 actives, et l'archive aurait coupé SMTP, Loginizer, Site Kit, etc. J'ai repris la liste du `package.json` de production.
  - Woodmart Core est retiré : le thème Woodmart n'est plus utilisé.
  - CookieAdmin est retiré de Casa Bazar : il doublerait le bandeau du thème.
- **Snippet WPCode 1383** (Casa Bazar), qui traduisait l'ancien thème Woodmart : passé en brouillon et retiré du cache WPCode (`_tools\wpcode-off.php`, risque R10).
- **Exclus des archives** : dossiers `.git`, capture d'e-mails locale, e-mails capturés, tout justificatif client.
- **Vérifications** :
  - 11 pages clés en 200, sans erreur PHP ni JS, avec le jeu d'extensions de la production (`_tools\health.mjs`) ;
  - lecture complète de chaque archive (en-têtes, tailles, bloc de fin), et la **CRC32** recalculée est identique à celle d'AI1WM (`_tools\wpress-verify.mjs`) ;
  - `database.sql` **réimporté sans erreur** dans une base MariaDB vierge (63 et 64 tables) ;
  - présence des modifications du jour contrôlée (`_tools\sql-check.mjs`). Les bases d'essai ont été supprimées.
- **En local** : les extensions de développement sont remises ; Site Kit, Kliken et SMTP restent désactivés pour ne rien envoyer.
- **Deux archives Casa Bazar intermédiaires** (export lancé deux fois par erreur, puis snippet encore actif) restent dans `casabazar\wp-content\ai1wm-backups\` : **ne pas les utiliser**.

---

## 12. Recherche de l'extension « fichiers lourds »

Demande : retrouver l'extension que vous aviez créée pour les fichiers lourds.

- **Trouvée** : « **CPS – Restaurar copia .wpress** » v1.0.0 (Casa Pergola Solar, 08/10/2026).
  - Deux exemplaires identiques : `Projets\cps\deploy\plugins\cps-restaurar-wpress\` et `Projets\cps\wordpress\wp-content\plugins\cps-restaurar-wpress\`.
- **Rôle** : restaurer un .wpress déjà déposé dans `wp-content/ai1wm-backups/` (par FTP ou cPanel) avec le moteur d'All-in-One WP Migration. Elle contourne ainsi la limite d'envoi de la version gratuite.
- Écran Outils › Restaurar copia .wpress : liste des copies, confirmation obligatoire, barre de progression, avertissement « tout le site est remplacé ».
- Aucune autre extension maison d'export ou de migration trouvée dans `Projets` et `Herd`. La recherche sur Bureau, Documents et Téléchargements a été arrêtée avant la fin, une fois l'extension trouvée.
- Je vous ai proposé de l'adapter à Casa Verde et Casa Bazar ; vous avez répondu « Non, c'est tout pour l'instant ». Rien n'a été modifié.

---

## 13. Inventaire des fichiers, scripts et versions

### Rapports (dossier du projet)
`INTEGRATION.md`, `MANQUANTS.md`, `RISQUES.md`, `inventaire/`, `design/`, `scripts/`, `livrables/`, et ce `JOURNAL-COMPLET.md`.

### Thèmes (git)

| Commit | Casa Verde | Casa Bazar |
|---|---|---|
| Intégration des maquettes | `1a2efa8` | `ae0e1c7` |
| Checkout et paiements | `31383d4` | `6095c77` |
| Icônes, listes, fiches, pays, checkout | `6018072` | `7835803` |
| Homes, cookies, justificatif | `a31106f` | `2119b38` |
| En-tête responsive | `f5dbee6` | `bfc43a5` |

### mu-plugins (git depuis le 27/10)
`casaverde-core.php` / `casabazar-core.php` (redirections 301, bouton de rétractation), `casa-orders.php`, `casa-orders/`, `casa-orders-site.php`, `casa-mail-local.php` (local seulement). Commits `9ee89e5` / `0dfbfe1`. Les changements de texte faits ensuite dans `casa-orders-site.php` (`proof_text_mail`, couleur de ligne) sont présents dans les archives .wpress.

### Scripts dans `_tools\`
- **Migration et contenus** : `mig-casaverde.php`, `mig-casabazar.php`, `clean-desc.php`, `clean-garbage.php`, `conv-pages.php`, `faq-pay.php`, `faq-pay2.php`, `ship-pages.php`, `legal-72h.php`, `elementor-insert.php`, `page-grep.php`, `page-outline.php`.
- **Configuration** : `setup-verde.php`, `setup-bazar.php`, `checkout-setup.php`, `checkout-texts.php`, `checkout-world.php`, `emails.php`, `brand-mail-setup.php`, `bacs-texts.php`, `toggle-gw.php`, `apply-wc-data.php`, `specs.php`, `widgets.php`, `states.php`, `plugins-set.php`, `wpcode-off.php`, `wpcode.php`.
- **Home et design** : `elementor-home.php`, `elementor-check.php`, `brand-assets.mjs`, `home-audit.php`.
- **Commandes** : `order-test.mjs`, `proof-test.mjs`, `casa-cycle.php`, `casa-bankchange.php`, `casa-affected.php`, `casa-revert.php`, `casa-stock.php`, `orders-check.php`, `sales.php`.
- **Contrôles** : `shot.mjs` (avec défilement pour les images différées), `overflow.mjs`, `lists-test.mjs`, `header-test.mjs`, `mail-shots.mjs`, `health.mjs`, `prod-audit.php`, `pay-audit.php`, `grep-pay.php`.
- **Export** : `ai1wm-export.php`, `wpress-verify.mjs`, `sql-check.mjs`.
- **Inventaire** : `inv.php`, `gen-inv.ps1`, `keys.php`.

### Sauvegardes de base (`_tools\dumps\`)
`<site>-apres-import-local.sql` (état d'origine), `<site>-integration-2026-10-27.sql`, `<site>-commandes-2026-10-27.sql`.

---

## 14. Erreurs rencontrées et corrections

| Problème | Correction |
|---|---|
| Ni MySQL ni WP-CLI sur la machine | MariaDB portable installé (avec votre accord) et WP-CLI téléchargé |
| Chemin de sortie de `wpress-extract` | option `--out` |
| `$home` en lecture seule dans PowerShell | variable renommée |
| BOM UTF-8 : nécessaire pour `gen-inv.ps1`, mais il cassait `json_decode` sur `redirects-map.json` | carte réécrite sans BOM ; contrôle des BOM sur tous les fichiers PHP, CSS et JS |
| Options `template` / `stylesheet` vides après import ; `after_switch_theme` en erreur | réglées à la main ; emplacements de menus posés directement |
| Classes Elementor ignorées | `css_classes` au lieu de `_css_classes` |
| Collisions de classes (`page`, `.cart`, `menu`) | `.page-body`, `.cart-link`, `foot-links` |
| `[hidden]` du bandeau cookies écrasé | `.consent [hidden]{display:none!important}` |
| Catégorie par défaut de Casa Bazar exclue alors qu'elle a des produits | exclusion retirée |
| Captures headless : largeur minimale ; captures du navigateur intégré mises à l'échelle | puppeteer-core avec émulation de viewport |
| Remontées du détecteur (padding, longueur de ligne, titres sautés, rythme, mots isolés) | corrigées une à une |
| Débordements mobiles (pilule de tuile, en-tête) | `flex-wrap`, menu en icône |
| Locale PT qui réordonnait le téléphone | filtres de priorité par locale |
| Tableaux du revendeur frères et non imbriqués | `cb_flatten_tables` réécrite |
| Expression régulière de `clean-desc` trop stricte ; phrases avec `<strong>` dans la FAQ | `<li[^>]*>`, scripts ciblés |
| Navigateur intégré refusé par vous | plus aucune utilisation |
| Polices non chargées pour les favicons (CORS depuis `about:blank`) | polices intégrées en data URI |
| Images vides sur les captures de home | chargement différé : défilement avant capture |
| **Tâche horaire sur l'historique** (section 10) | statuts rétablis, garde-fou `casa_orders_since` |
| Compteur des favoris mal placé dans le menu | `position:static` |
| `wp_update_post` refusé sur le snippet WPCode (titre vide), alors que le script annonçait la désactivation | passage en brouillon direct, cache WPCode reconstruit, export refait ; le script échoue désormais si le snippet est encore actif |
| Export Casa Bazar lancé deux fois | archive finale identifiée et copiée dans `livrables/` ; les doublons sont signalés |
| Vérificateur .wpress très lent (expression régulière quadratique) puis bloc de fin non reconnu (CRC32 ajoutée par AI1WM 7.x) | lecture des champs corrigée, CRC32 recalculée et comparée |
| Recherche de fichiers trop lente sur tout le profil | recherche ciblée par projet, recherche globale arrêtée |

---

## 15. Ce qui n'a pas été fait

- Mesure **Lighthouse**.
- Ouverture réelle de l'**éditeur Elementor** dans un navigateur connecté : seul le chargement des documents a été testé en ligne de commande.
- **E-mails réels** via SMTP : ils ont seulement été capturés en local.
- **Facture définitive** (extension de facturation, numérotation légale ; au Portugal, logiciel certifié par l'AT) : les e-mails envoient une proforma, une confirmation et un bon de livraison sans valeur fiscale.
- **Traduction** des 10 descriptions Casa Verde encore en portugais (V9) et crawl de contrôle des langues.
- **Réactivation** du bandeau CookieAdmin : non voulue, le thème a son propre bandeau.
- **Mise en ligne** : rien n'a été importé, conformément à votre règle de validation écrite.

---

## 16. À faire par le client avant la mise en ligne

**Bloquant**
- **C15** — Casa Bazar : IBAN, banque, titulaire. Le virement est le seul moyen de paiement, et les relances disent pour l'instant « coordonnées indisponibles ».
- **C14 / R9** — Casa Verde : le compte de virement est au nom de « ANGEL Silva-Lenha », pas de CASA VERDE 42 SL.
- **B9** — Les 24 photos de pellets portent le logo d'une autre entreprise (distribuidor-biomasa.es) et des badges : bloquant pour Merchant Center et pour les droits.
- **5 poids incohérents** sur Casa Verde : €/kg non affiché, obligation 98/6/CE.
- **Import** :
  - il **écrase la base de production** : les commandes, clients et messages arrivés depuis le 26/10/2026 seront perdus ; il faut les exporter avant ;
  - les archives dépassent la limite d'import de la version gratuite d'AI1WM : il faut l'extension Unlimited, l'extension « CPS – Restaurar copia .wpress » adaptée, ou une limite d'envoi relevée ;
  - après l'import, vérifier `casa_orders_since` et vider la corbeille des commandes de test.

**À valider**
- **R2 / V1 / B3** — Fiscalité : IVA ou IGIC pour Casa Verde ; taux de 23 % et `calc_taxes` pour Casa Bazar.
- **C18 / C3 / B6** — Tarifs de transport hors pays d'origine, et barème réel.
- **C20** — Relecture juridique de la clause des 72 h dans les CGV.
- **R11** — La commande passe « en cours » sur simple justificatif : vérifier l'encaissement avant d'expédier. La clôture automatique ne prouve pas la livraison.
- **R12** — Prévoir une vraie tâche cron sur le serveur pour les relances.
- **C17** — Outils d'analyse réellement utilisés (Site Kit, Kliken, Mailchimp) : à déclarer dans la politique de cookies.
- **V7 / R5** — Administrateurs inconnus `Agent` et `Staff` ; extensions « pro » sans licence vérifiable ; FileOrganizer, Better Search Replace, Duplicate Page, External Importer à supprimer.
- **V2, V8, B4, B5, B7** — Registro mercantil, hoja de reclamaciones Canarias, Livro de Reclamações, entité RAL, objet social de Casa Bazar.
- **C1, C2, C6, C7, C9, C16, B1, B2, B10, B11, V3 à V6, V9 à V11** — GTIN, poids, comptes Google, flux Merchant, hébergement, facturation, preuves ENplus, historique des prix, prix des lots de 6 palettes, fiches barbecues, images.
- **R1** — Comptes Merchant Center, moyens de paiement et coordonnées séparés pour chaque entité.

Le détail de chaque point est dans [MANQUANTS.md](MANQUANTS.md) et [RISQUES.md](RISQUES.md).

---

## 17. Liste des demandes, dans l'ordre

1. Brief de refonte des deux boutiques (phases 0 à 8, interdictions, points d'arrêt).
2. Installer MariaDB portable hors Herd, puis continuer.
3. Travailler seul, sans attendre d'approbation, et finir.
4. « Try again », deux fois, après des échecs d'outil.
5. Brief design : directions A « Hogar cálido » et B « Ficha técnica ».
6. Réponses : Casa Bazar en portugais ; 3 €/kg les plus bas dans le héros.
7. Validation des deux directions, puis intégration WordPress.
8. Champ libre pour gérer et réviser, autorisations à prendre seul.
9. Travailler en autonomie, sans le navigateur intégré.
10. Analyse de casapergolasolar.com, bouton final du checkout, moyens de paiement cités automatiquement.
11. Textes cloîtrés sur les fiches, icônes panier / favoris / comparer / compte selon GMC, vente dans tous les pays avec livraison gratuite seulement dans le pays d'origine, checkout facilité.
12. Retirer le comparatif de pellets de la home Casa Bazar ; plus de sections produits ; justificatif de virement ; relances 24 h ; annulation 72 h ; clôture automatique ; e-mails proforma et bon de livraison ; favicons.
13. Corriger la FAQ et les CGV (72 h) ; préparer les .wpress sans erreur.
14. En cours de tâche : en-tête responsive (menu à gauche sans texte, logo au centre, icônes rangées dans le menu, panier seul).
15. Retrouver l'extension créée pour les fichiers lourds.
16. « Non, c'est tout pour l'instant. »
17. Mettre dans un document tout ce qui a été fait : ce fichier.

---

# Partie 2 — Boutique WordPress de désumidificateurs (thème « Ar Seco »)

Période : 2026-10-31. Travail fait dans un conteneur cloud (Docker : WordPress 7.1.4, PHP 8.3, MariaDB 11, WP-CLI), dossier `wordpress-env/`, branche `claude/awesome-edison-u628oq`. **Rien n'a été mis en ligne.** Les règles de la section 1 (rien d'inventé, aucune fausse urgence, aucun prix barré sans preuve, aucun lien `#`, tout manque dans `MANQUANTS.md`) ont été appliquées.

## 18. Demandes de cette partie, dans l'ordre

1. Simuler et créer un environnement WordPress.
2. Créer une boutique WooCommerce reprenant le design joint (maquette « WEAVE »), pour l'électroménager et surtout les déshumidificateurs, en portugais, avec les informations légales inspirées de casa-bazar.com et les informations d'entreprise laissées vides.
3. Fournir l'archive `woocommerce.11.2.1.zip`, savoir où consulter le travail, continuer avec WooCommerce et tout noter au journal.

## 19. Environnement Docker

- Le démon Docker n'était pas lancé : démarré à la main (`dockerd`). Il ne redémarre pas seul dans une nouvelle session.
- Images : `wordpress:php8.3-apache`, `mariadb:11`, `wordpress:cli-php8.3` (Docker Hub joignable).
- `docker-compose.yml` : services `db`, `wordpress`, `wpcli` (profil `tools`), volumes `db_data` et `wp_data`, thème monté depuis `wp-content/themes/arseco`, archive WooCommerce montée en lecture seule depuis `vendor/`.
- `setup.sh` : démarre, attend, installe WordPress via WP-CLI (idempotent). `setup-loja.sh` : installe WooCommerce, active le thème, peuple le site.
- Réseau : wordpress.org, GitHub, casa-bazar.com et les serveurs MCP « design » sont refusés par le proxy (403). Les conteneurs n'ont aucun accès sortant. npm, Packagist et Docker Hub fonctionnent.

## 20. Thème « Ar Seco » (`wp-content/themes/arseco`)

- **Design** repris de la maquette : crème `#efede7`, anthracite `#2a2927`, terracotta `#a97c65`, polices Inter Tight auto-hébergées (installées depuis npm), pastilles, cartes arrondies, titre géant en fond de héros et de pied de page, section sombre avec fiche technique, FAQ en accordéon.
- **Page d'accueil** : héros, « Sobre », « O que procurar » (4 onglets), produits avec fiche technique, FAQ, pied de page.
- **Illustration SVG** d'un déshumidificateur en trois teintes (`inc/art.php`), seule image disponible. Aucune photo n'existe.
- **Dados da empresa** (Aspect > Dados da empresa) : 12 champs (marque, raison sociale, NIF, adresse, registre, capital, e-mail, téléphone, horaires, entité RAL, IBAN). Vides, ils s'affichent `[a preencher]` en surbrillance.
- **12 pages** institutionnelles et légales (texte de base, relecture juridique requise) : sobre nós, contactos, perguntas frequentes, entregas, devoluções, livre resolução, garantia (3 ans, DL 84/2021), termos e condições, política de privacidade, política de cookies, Livro de Reclamações, resolução de litígios.
- **Formulaire de rétractation en ligne** (`[livre_resolucao]`) : nonce, champ piège anti-robots, enregistrement en base (type privé « Livre resolução », visible dans l'administration), e-mails en second plan.
- **Bandeau cookies** : boutons Rejeitar / Configurar / Aceitar de même poids, choix gardé dans le navigateur, lien « Configurar cookies » dans le pied. Aucun service d'analyse n'est chargé.
- **WooCommerce** : bouton final « Encomenda com obrigação de pagar » ; prix barrés neutralisés (Omnibus) ; avis désactivés ; onglet « Ficha técnica » ; illustration à la place du carré gris.
- **Catalogue d'exemple** (4 produits, titres « (exemplo) », prix inventés, caractéristiques « Por confirmar ») : à remplacer.
- **Traduction** : l'archive officielle `pt_PT` n'est pas téléchargeable. Dictionnaire de secours dans `inc/i18n.php` (≈150 chaînes WooCommerce), noms de pays via ICU, textes de confidentialité et de CGV de la caisse définis dans la base. Pages WooCommerce et URL en portugais : `/loja/`, `/carrinho/`, `/finalizar-compra/`, `/minha-conta/`, `/produto/…`, `/categoria/…`, `/encomenda-recebida/`.

## 21. Installation de WooCommerce 11.2.1

- Archive fournie par vous (`woocommerce.11.2.1.zip`, Automattic), examinée dans un dossier vide avant usage, copiée dans `vendor/` (ignoré par git, 18 Mo). **Intégrité non vérifiable** (pas de somme de contrôle officielle accessible).
- Installée avec `wp plugin install /vendor/woocommerce.zip --activate`.

## 22. Vérifications

- **Parcours d'achat complet** en Chromium (`flow.js`) : loja, catégorie, fiche, ajout au panier, panier, caisse, commande terminée. Réalisé avec un paiement « à la livraison » et une zone d'envoi **temporaires**, retirés ensuite avec les 4 commandes de test.
- Balayage du texte visible de chaque page du parcours : plus aucune chaîne anglaise hors liste de pays traduite.
- Aucun débordement horizontal à 390 px (loja, fiche, panier, caisse, compte, CGV, livre resolução). Aucune erreur JavaScript.
- Bandeau cookies (rejeter, mémoriser, rouvrir), FAQ, onglets, formulaire de rétractation : testés au navigateur.
- **Non testé** : compte client connecté, e-mails WooCommerce (encore en anglais), cupons, variations, stock, Lighthouse, Safari/Firefox, lecteur d'écran.

## 23. Erreurs rencontrées et corrections

| Problème | Correction |
|---|---|
| wordpress.org, GitHub bloqués : ni WordPress, ni WooCommerce, ni pack de langue téléchargeables | image Docker officielle pour WordPress ; archive WooCommerce fournie par vous ; dictionnaire de secours |
| Démon Docker absent | lancé avec `dockerd` |
| `.env` : `WP_TITLE=WP Simulation` sans guillemets, `source` plantait | valeur entre guillemets |
| `|| true` masquait l'échec du pack de langue | avertissement explicite |
| `woo.php` : garde finale empêchait l'enregistrement des filtres (`plugins_loaded` déjà passé) | filtres enregistrés directement |
| Montage `themes/custom` : thème imbriqué non détecté | montage direct `themes/arseco` |
| Zip WooCommerce en `0600` : illisible par WP-CLI (utilisateur 33) | `chmod 644` |
| Pages WooCommerce créées en anglais (`shop`, `cart`…) | slugs, titres, bases d'URL, points de terminaison renommés en portugais ; seed exécuté en deux passes (les bases d'URL ne s'appliquent qu'à la requête suivante) |
| Collision de classe : ma section d'accueil `.products` stylait aussi la liste WooCommerce (titres illisibles, bande noire) | renommée `.catalogo` |
| Violet WooCommerce écrasait la palette | surcharges ciblées |
| Caisse non stylée : la page passe par `page.php`, sans classe `woo` | classe `woo` ajoutée sur panier, caisse et compte ; deux colonnes |
| « Additional information » traduit « Ficha técnica » en caisse | chaîne distincte : « Informação adicional » ; l'onglet produit est renommé par filtre |
| Pluriel « Showing all %1$d results » non traduit (clé `%1$d`, pas `%d`) | clé corrigée |
| Galerie produit pâle (opacité initiale WooCommerce) | `opacity:1` forcé |
| Texte du bandeau de confidentialité en anglais (enregistré en base à l'activation) | options redéfinies au seed |
| Mobile : pastilles du héros sur la carte, titre rogné | positions revues, taille 27vw |
| Copyright du pied recouvert par le titre géant | marge corrigée |
| **Titre géant du pied de page recouvrait les liens** : « Configurar cookies » et autres non cliquables | `pointer-events:none` sur le titre, liens au-dessus |
| **Formulaire de rétractation : erreur affichée sans SMTP, demande perdue** | enregistrement en base avant tout e-mail |
| Message de succès affirmait un e-mail envoyé alors qu'il peut échouer | formulation exacte |

## 24. Ce qui n'a pas été fait

- Aucun moyen de paiement ni tarif d'envoi réels (inconnus).
- Pas de photos de produits, pas de vrais produits ni de fiches techniques.
- Pas de pack de langue officiel `pt_PT` ni de traduction des e-mails WooCommerce.
- Pas de facturation certifiée (au Portugal, logiciel certifié par l'AT).
- Pas de comparaison avec le texte de casa-bazar.com (inaccessible) : la structure légale vient de la partie 1 de ce journal.
- Mise en ligne : non, et non demandée.

## 25. À faire par le client

**Bloquant** : données d'entreprise ; nom de marque (titre géant « MARCA ») ; vrais produits, prix et fiches ; photos avec droits ; texte juridique relu ; délais et frais d'envoi ; au moins un moyen de paiement ; pack de langue pt_PT.
**À valider** : IVA et `calc_taxes` (désactivé), obligations REEE, inscription au Livre de Réclamations électronique, entité RAL, SMTP pour les e-mails.
Détail dans `MANQUANTS.md`.

## 26. Où consulter

- Code, captures et ce journal : dépôt `vianney64/claude`, branche `claude/awesome-edison-u628oq`, dossier `wordpress-env/` (captures dans `wordpress-env/captures/`).
- Le site tourne dans le conteneur de cette session (`http://localhost:8080`) : **inaccessible depuis votre machine**, et il disparaît avec la session. Pour le voir chez vous : `git clone`, mettre l'archive WooCommerce dans `wordpress-env/vendor/woocommerce.zip`, puis `./setup-loja.sh` (Docker requis).

## 27. Archive .wpress téléchargeable

Demande : fournir le site en fichier `.wpress`.

- All-in-One WP Migration n'est pas téléchargeable ici. Générateur écrit : `tools/build-wpress.py` (format : en-têtes de 4377 octets, fin par un bloc nul).
- Contenu : `package.json`, `database.sql` (préfixe remplacé par `SERVMASK_PREFIX_`, collations MariaDB 11 neutralisées, ligne « sandbox mode » retirée), thème `arseco`, WooCommerce 11.2.1 (copié depuis le conteneur), médiathèque. Exclus : journaux WooCommerce, caches, `.env`. Résultat : `ar-seco-2026-10-31.wpress`, 82,1 Mo, 5519 fichiers.
- Mot de passe du compte `admin` remplacé par une valeur aléatoire avant export (le mot de passe de développement n'est pas dans l'archive).
- Vérifications : lecteur indépendant (EOF exact, 52 tables, 0 `wp_` résiduel, thème identique octet pour octet) ; import de la base dans MySQL 8.0.46 vierge sans erreur, 22 pages et produits publiés.
- **Non vérifié** : import avec le vrai plugin ; format exact de `package.json` attendu par sa version. Procédure de secours dans le README.
- Erreurs : le démon Docker s'était arrêté (relancé, volumes intacts) ; le lecteur `wpress-extract` (npm) a été bloqué par le système de permissions, remplacé par un lecteur maison.
