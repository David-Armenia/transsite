# Architecture (TransEscort theme)

```mermaid
flowchart TD
  subgraph WP[WordPress Core]
    H[Hooks: init / template_redirect / wp_ajax_* / admin]
  end

  subgraph Theme[Theme: transescort]
    F[functions.php\nloads /inc modules]

    SEC[inc/security.php\nbrute-force helpers]
    ENQ[inc/enqueue.php\nassets + localize]
    AJAX[inc/ajax.php\nAJAX endpoints]
    SET[inc/setup.php\nsupports + role/caps]
    CPTP[inc/cpt-profile.php\nCPT Profile]
    CPTR[inc/cpt-request.php\nCPT Request + admin]
    HELP[inc/helpers.php\nownership + linked ids]
    PP[inc/personal-profile.php\nfrontend handlers]
    AP[inc/admin-profile.php\nadmin metaboxes]
    AR[inc/admin-requests.php\nadmin list tweaks]
    AUTH[inc/auth-enhancements.php\noptional enhanced auth]
  end

  subgraph DB[WP Database]
    UM[user_meta\n_linked_profile_id]
    PM1[post_meta Profile\n_profile_user_id]
    PM2[post_meta Profile\n_profile_gallery_ids]
    RM[post_meta Request\n_request_*]
  end

  H --> F
  F --> SEC
  F --> ENQ
  F --> AJAX
  F --> SET
  F --> CPTP
  F --> CPTR
  F --> HELP
  F --> PP
  F --> AP
  F --> AR
  F -.optional.-> AUTH

  HELP --> UM
  HELP --> PM1
  PP --> PM2
  AJAX --> RM
  CPTR --> RM
