# Documentation SYGEP

| Document | Contenu |
|---|---|
| [GUIDE-COMPLET.md](GUIDE-COMPLET.md) | Guide complet : installation, rôles et droits, un chapitre par module, règles de gestion, exploitation, architecture. |
| [CAHIER-DE-RECETTE.md](CAHIER-DE-RECETTE.md) | 293 cas de test lisibles (étapes, résultat attendu, résultat de la pré-recette automatique). |
| [CAHIER-DE-RECETTE.xlsx](CAHIER-DE-RECETTE.xlsx) | Même cahier en classeur Excel : une feuille par module, liste de résultats, synthèse automatique, anomalies, PV. |
| [livrables/SYGEP-Guide-complet.pdf](livrables/SYGEP-Guide-complet.pdf) / `.docx` | Le guide complet, mis en forme (page de garde, sommaire, captures) : PDF pour lire/imprimer, Word pour modifier. |
| [livrables/SYGEP-Cahier-de-recette.pdf](livrables/SYGEP-Cahier-de-recette.pdf) / `.docx` | Le cahier de recette mis en forme, en paysage, avec synthèse et procès-verbal à signer. |
| `img/` | Captures d'écran utilisées par le guide. |
| `outils/` | Sources : `cas_de_test.py` (cas), `pre_recette.json` (résultats auto), `generer_recette.py` (`generer_recette.py` régénère le cahier Excel ; `generer_documents.py` régénère les PDF/Word : nécessite `markdown beautifulsoup4 python-docx pypdf` et Chromium + `playwright-core`). |
