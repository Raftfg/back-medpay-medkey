# Plan d'Action - Transformation SIH 2026
## Medkey → Système d'Information Hospitalier Complet

**Date:** Octobre 2025  
**Durée totale:** 18-24 mois  
**Budget estimé:** 590-730K€

---

## 📊 Vue d'ensemble

### État actuel vs Objectif

| Aspect | Actuellement | Objectif 2026 | Progression |
|--------|--------------|---------------|-------------|
| **Modules fonctionnels** | 18 modules | 30+ modules | 60% |
| **Interopérabilité** | Aucune | HL7 FHIR + DICOM | 0% |
| **IA intégrée** | Non | Oui (6 fonctionnalités) | 0% |
| **DME complet** | Partiel | Complet | 40% |
| **Portail patient** | Non | Oui + Télémédecine | 0% |
| **Standards santé** | Non | Conforme international | 0% |

---

## 🎯 Objectifs SMART

### Objectif 1 : Interopérabilité (T1 2026)
✅ Implémenter HL7 FHIR R4 pour 100% des échanges cliniques  
✅ Intégrer DICOM pour l'imagerie  
✅ Adopter SNOMED CT et ICD-10 pour la terminologie  
**KPI:** Tous les mouvements patients échangeables en HL7

### Objectif 2 : CPOE/CDS avec IA (T2 2026)
✅ Prescriptions électroniques 100% digitales  
✅ Aide décision clinique avec IA  
✅ Alertes interactions médicamenteuses  
**KPI:** 0 prescriptions papier, 95% prescriptions vérifiées IA

### Objectif 3 : Portail Patient (T3-T4 2026)
✅ 80% patients inscrits sur portail  
✅ Téléconsultations opérationnelles  
✅ Application mobile déployée  
**KPI:** 50% rendez-vous pris en ligne

### Objectif 4 : BI Avancé (2027)
✅ Data Warehouse opérationnel  
✅ 30+ KPI temps réel  
✅ Prédictions IA (réadmissions, risques)  
**KPI:** Décisions basées données 90% du temps

---

## 📅 Roadmap détaillée

### PHASE 1 : Fondations et Interopérabilité
**Période:** Janvier - Mars 2026 (T1)  
**Durée:** 3 mois  
**Équipe:** 4 développeurs + 1 architecte  
**Budget:** 80-100K€

#### Sprint 1-2 (Semaines 1-4) : Infrastructure
- [ ] Migration PostgreSQL
- [ ] Configuration Docker + Kubernetes
- [ ] Mise en place S3 (stockage objet)
- [ ] CI/CD complet
- [ ] Environnements (dev/test/staging/prod)

#### Sprint 3-4 (Semaines 5-8) : HL7 FHIR
- [ ] Développement connecteur HL7 v2 (ADT)
- [ ] API FHIR R4 (Patient, Encounter, Observation)
- [ ] Tests interopérabilité
- [ ] Documentation technique

#### Sprint 5-6 (Semaines 9-12) : Sécurité
- [ ] Chiffrement AES-256 données sensibles
- [ ] Audit trails complets
- [ ] SSO OpenID Connect
- [ ] Tests sécurité (pentesting)

**Livrables:**
✅ Infrastructure cloud-ready  
✅ API FHIR R4 fonctionnelle  
✅ Sécurité niveau hospitalier  
✅ Documentation technique complète

---

### PHASE 2 : DME et Prescriptions Intelligentes
**Période:** Avril - Juillet 2026 (T2)  
**Durée:** 4 mois  
**Équipe:** 5 développeurs + 1 expert IA  
**Budget:** 120-150K€

#### Sprint 7-8 (Semaines 13-16) : Enrichissement DME
- [ ] Antécédents médicaux complets
- [ ] Allergies et intolérances
- [ ] Vaccinations
- [ ] Timeline patient interactive
- [ ] Intégration SNOMED CT/ICD-10

#### Sprint 9-11 (Semaines 17-24) : CPOE/CDS
- [ ] Module prescriptions électroniques
- [ ] Base de données médicaments (Vidal/Thériaque)
- [ ] Moteur d'aide à la décision
- [ ] **IA : Détection interactions médicamenteuses**
- [ ] **IA : Suggestions diagnostiques**
- [ ] Alertes cliniques (allergies, posologie)
- [ ] Protocoles de soins

#### Sprint 12 (Semaines 25-28) : Pharmacie avancée
- [ ] Gestion lots et numéros de série
- [ ] Traçabilité complète médicaments
- [ ] Dispensation nominative
- [ ] Lien prescriptions → dispensation
- [ ] Alertes péremption

**Livrables:**
✅ DME enrichi conforme standards  
✅ CPOE avec IA fonctionnel  
✅ 0 prescriptions papier  
✅ Pharmacie traçable 100%

---

### PHASE 3 : Laboratoire et Imagerie
**Période:** Août - Novembre 2026 (T3)  
**Durée:** 4 mois  
**Équipe:** 5 développeurs + 1 expert DICOM  
**Budget:** 150-180K€

#### Sprint 13-15 (Semaines 29-36) : LIS (Laboratory Information System)
- [ ] Module commandes examens
- [ ] Gestion prélèvements
- [ ] Saisie et validation résultats
- [ ] Interface automates (HL7)
- [ ] Nomenclature LOINC
- [ ] Valeurs de référence
- [ ] Transmission automatique DME
- [ ] Graphiques évolution résultats

#### Sprint 16-19 (Semaines 37-48) : RIS/PACS
- [ ] Module planification examens imagerie
- [ ] Serveur PACS
- [ ] Stockage images DICOM
- [ ] Visualiseur DICOM web
- [ ] Compte-rendu radiologie structuré
- [ ] **IA : Détection anomalies radiologiques**
- [ ] Intégration HL7 imagerie
- [ ] Gestion modalités (Scanner, IRM, Radio, Echo)

**Livrables:**
✅ LIS complet et opérationnel  
✅ PACS avec visualiseur DICOM  
✅ IA analyse images (prototype)  
✅ Interopérabilité laboratoire/imagerie

---

### PHASE 4 : Portail Patient et Télémédecine
**Période:** Décembre 2026 - Mars 2027 (T4-T1 Y2)  
**Durée:** 4 mois  
**Équipe:** 4 développeurs (2 backend, 1 frontend, 1 mobile)  
**Budget:** 100-120K€

#### Sprint 20-21 (Semaines 49-56) : Portail Patient Web
- [ ] Authentification patient sécurisée (2FA)
- [ ] Tableau de bord patient
- [ ] Accès DME personnel (lecture seule)
- [ ] Prise de rendez-vous en ligne
- [ ] Téléchargement documents (comptes-rendus, ordonnances)
- [ ] Historique consultations
- [ ] Gestion profil et préférences
- [ ] Messagerie sécurisée patient-médecin

#### Sprint 22-23 (Semaines 57-64) : Téléconsultation
- [ ] Visioconférence sécurisée (WebRTC)
- [ ] Salle d'attente virtuelle
- [ ] Partage documents en temps réel
- [ ] Prescription à distance
- [ ] Paiement en ligne
- [ ] Conformité réglementaire télémédecine
- [ ] **Chatbot IA assistant patient**

#### Sprint 24 (Semaines 65-68) : Application Mobile (mHealth)
- [ ] Application mobile iOS/Android
- [ ] Synchronisation avec portail web
- [ ] Notifications push
- [ ] Géolocalisation services
- [ ] Mode hors ligne
- [ ] Partage données santé (Apple Health, Google Fit)

**Livrables:**
✅ Portail patient 100% fonctionnel  
✅ Téléconsultation opérationnelle  
✅ Application mobile déployée  
✅ 50% patients inscrits objectif

---

### PHASE 5 : Business Intelligence et Pilotage
**Période:** Avril - Juin 2027 (T2 Y2)  
**Durée:** 3 mois  
**Équipe:** 3 développeurs (1 data engineer, 2 dev)  
**Budget:** 60-80K€

#### Sprint 25-26 (Semaines 69-76) : Data Warehouse
- [ ] Architecture ETL (Extract-Transform-Load)
- [ ] Centralisation données (Data Warehouse)
- [ ] Modèle dimensionnel (faits et dimensions)
- [ ] Pipeline automatisé
- [ ] Historisation données

#### Sprint 27-28 (Semaines 77-84) : Dashboards et KPI
- [ ] **KPI Cliniques:** Taux occupation, durée séjour, réadmissions
- [ ] **KPI Financiers:** CA, impayés, rentabilité par service
- [ ] **KPI Qualité:** Satisfaction patients, délais rendez-vous
- [ ] **KPI Opérationnels:** Taux utilisation équipements, stocks
- [ ] Tableaux de bord personnalisables par rôle
- [ ] Rapports automatisés (quotidien, hebdo, mensuel)
- [ ] Alertes intelligentes

#### Sprint 29 (Semaines 85-88) : Analytics IA
- [ ] **Prédiction réadmissions** (ML)
- [ ] **Détection risques complications** (ML)
- [ ] **Optimisation plannings** (algorithmes)
- [ ] **Détection fraudes** (anomaly detection)
- [ ] Tableaux de bord prédictifs

**Livrables:**
✅ Data Warehouse opérationnel  
✅ 30+ KPI temps réel  
✅ Dashboards interactifs  
✅ 3 modèles IA prédictifs

---

### PHASE 6 : Modules Complémentaires
**Période:** Juillet - Septembre 2027 (T3 Y2)  
**Durée:** 3 mois  
**Équipe:** 3-4 développeurs  
**Budget:** 80-100K€

#### Sprint 30-31 (Semaines 89-96) : Agenda Médical
- [ ] Planning médecins et ressources
- [ ] Prise de rendez-vous multi-canaux
- [ ] Gestion disponibilités
- [ ] Rappels automatiques (SMS/Email)
- [ ] Planning bloc opératoire
- [ ] Optimisation créneaux (IA)

#### Sprint 32 (Semaines 97-100) : Maintenance Équipements
- [ ] Inventaire équipements biomédicaux
- [ ] Planification maintenance préventive
- [ ] Suivi interventions
- [ ] Historique pannes
- [ ] Alertes calibration/métrologie

#### Sprint 33 (Semaines 101-104) : Surveillance Épidémiologique
- [ ] Intégration DHIS2
- [ ] Déclaration maladies à déclaration obligatoire
- [ ] Tableaux de bord épidémiologiques
- [ ] Alertes épidémies

**Livrables:**
✅ Agenda médical complet  
✅ Module maintenance  
✅ Surveillance épidémiologique (DHIS2)

---

## 🔧 Spécifications techniques

### Stack technologique

#### Backend
- **Framework:** Laravel 10.x → 11.x
- **Base de données:** PostgreSQL 15+
- **Cache:** Redis
- **Queue:** Redis + Horizon
- **API:** RESTful + GraphQL (FHIR)
- **Real-time:** WebSockets (Laravel Echo)

#### Frontend
- **Framework:** Vue.js 3 + Composition API
- **UI Library:** Vuetify 3 / PrimeVue
- **État:** Pinia
- **Build:** Vite
- **Mobile:** Flutter ou React Native

#### DevOps
- **Conteneurisation:** Docker + Kubernetes
- **CI/CD:** GitLab CI ou GitHub Actions
- **Monitoring:** Prometheus + Grafana
- **Logs:** ELK Stack (Elasticsearch, Logstash, Kibana)
- **Stockage:** MinIO (S3 compatible)

#### Interopérabilité
- **HL7 v2:** Mirth Connect ou HAPI FHIR
- **FHIR R4:** HAPI FHIR Server
- **DICOM:** Orthanc PACS
- **Terminologies:** UMLS Metathesaurus

#### Intelligence Artificielle
- **Framework:** TensorFlow / PyTorch
- **NLP:** spaCy + Transformers (BERT médical)
- **ML Ops:** MLflow
- **Serveur IA:** FastAPI (Python)

---

## 👥 Organisation équipe

### Équipe permanente (10-12 personnes)

#### Management
- **Chef de Projet / PO** (1) - Coordination générale
- **Scrum Master** (1) - Méthodologie Agile

#### Développement
- **Architecte Technique** (1) - Architecture et normes
- **Dev Backend Laravel Senior** (3)
- **Dev Frontend Vue.js** (2)
- **Dev Mobile Flutter** (1)
- **Data Engineer / BI** (1)

#### Qualité et Ops
- **DevOps Engineer** (1)
- **QA / Testeur** (1)
- **Expert Sécurité** (0.5 ETP)

### Consultants ponctuels

| Expert | Phase(s) | Durée | Budget |
|--------|---------|-------|--------|
| Expert HL7 FHIR | Phase 1-3 | 6 mois | 60K€ |
| Expert DICOM / PACS | Phase 3 | 4 mois | 40K€ |
| Expert IA / ML | Phase 2-5 | 8 mois | 80K€ |
| Expert UX/UI Santé | Phase 4 | 2 mois | 20K€ |
| Expert Réglementaire | Toutes phases | Audit mensuel | 30K€ |

---

## 💰 Budget détaillé

### Coûts de développement

| Poste | Quantité | Coût unitaire | Durée | Total |
|-------|----------|---------------|-------|-------|
| Chef de Projet | 1 | 6K€/mois | 24 mois | 144K€ |
| Architecte | 1 | 7K€/mois | 24 mois | 168K€ |
| Dev Senior | 3 | 5K€/mois | 24 mois | 360K€ |
| Dev Junior | 3 | 3.5K€/mois | 18 mois | 189K€ |
| Data Engineer | 1 | 5.5K€/mois | 12 mois | 66K€ |
| DevOps | 1 | 5K€/mois | 24 mois | 120K€ |
| QA | 1 | 4K€/mois | 24 mois | 96K€ |
| **Sous-total équipe** | | | | **1,143K€** |

### Consultants externes

| Consultant | Durée | Coût | Total |
|------------|-------|------|-------|
| Expert HL7 FHIR | 6 mois | 10K€/mois | 60K€ |
| Expert DICOM | 4 mois | 10K€/mois | 40K€ |
| Expert IA | 8 mois | 10K€/mois | 80K€ |
| Expert UX/UI | 2 mois | 10K€/mois | 20K€ |
| Expert Réglementaire | 24 audits | 1.25K€ | 30K€ |
| **Sous-total consultants** | | | **230K€** |

### Infrastructure et licences

| Poste | Coût mensuel | Durée | Total |
|-------|--------------|-------|-------|
| Cloud (AWS/GCP) | 3K€ | 24 mois | 72K€ |
| Licences Vidal/Thériaque | 2K€ | 24 mois | 48K€ |
| Licences SNOMED CT | - | Une fois | 10K€ |
| Serveur PACS | - | Une fois | 15K€ |
| Outils dev (JetBrains, etc.) | 0.5K€ | 24 mois | 12K€ |
| **Sous-total infra** | | | **157K€** |

### Autres coûts

| Poste | Total |
|-------|-------|
| Formation équipe | 30K€ |
| Formation utilisateurs | 40K€ |
| Tests/Audit sécurité | 25K€ |
| Documentation | 15K€ |
| Imprévus (15%) | 240K€ |
| **Sous-total autres** | **350K€** |

### Budget total

| Catégorie | Montant |
|-----------|---------|
| Équipe permanente | 1,143K€ |
| Consultants | 230K€ |
| Infrastructure | 157K€ |
| Autres | 350K€ |
| **TOTAL** | **1,880K€** |

---

## 📈 KPI de suivi du projet

### KPI Projet

| KPI | Cible | Mesure |
|-----|-------|--------|
| **Respect délais** | >90% sprints à l'heure | Hebdomadaire |
| **Budget respecté** | ±10% budget prévisionnel | Mensuel |
| **Vélocité équipe** | 80-100 story points/sprint | Par sprint |
| **Qualité code** | >80% couverture tests | Continu |
| **Dette technique** | <10% du code | Mensuel |
| **Bugs critiques** | <5 en production | Hebdomadaire |

### KPI Métier (post-déploiement)

| KPI | Cible | Phase |
|-----|-------|-------|
| **Taux adoption portail patient** | >50% | Phase 4 |
| **Prescriptions électroniques** | 100% | Phase 2 |
| **Temps saisie DME** | -30% | Phase 2 |
| **Délai résultats labo** | -50% | Phase 3 |
| **Satisfaction utilisateurs** | >4/5 | Toutes phases |
| **Économies générées** | >200K€/an | Année 2 |

---

## ⚠️ Gestion des risques

### Risques majeurs et plans de mitigation

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| **Complexité interopérabilité** | 🔴 Élevée | 🔴 Élevé | • Recrutement expert HL7/FHIR<br>• POC validation rapide<br>• Tests interop continus |
| **Manque compétences IA** | 🟡 Moyenne | 🟡 Moyen | • Formation équipe interne<br>• Consultant IA expérimenté<br>• Commencer par use cases simples |
| **Résistance au changement** | 🔴 Élevée | 🔴 Élevé | • Implication utilisateurs dès le début<br>• Formation intensive<br>• Champions internes<br>• Communication continue |
| **Migration données** | 🟡 Moyenne | 🔴 Élevé | • Plan de migration détaillé<br>• Tests exhaustifs<br>• Rollback possible<br>• Migration progressive |
| **Performance système** | 🟡 Moyenne | 🟡 Moyen | • Tests de charge réguliers<br>• Optimisation continue<br>• Scalabilité horizontale |
| **Conformité RGPD/Sécurité** | 🔴 Élevée | 🔴 Élevé | • Audit sécurité externe<br>• DPO dédié<br>• Formation RGPD équipe<br>• Privacy by design |
| **Dérive planning** | 🔴 Élevée | 🟡 Moyen | • Méthodologie Agile stricte<br>• Sprints courts (2 semaines)<br>• Revues régulières<br>• Buffer 20% sur estimations |
| **Turnover équipe** | 🟡 Moyenne | 🟡 Moyen | • Documentation exhaustive<br>• Pair programming<br>• Knowledge sharing<br>• Rémunération attractive |

---

## ✅ Critères d'acceptation

### Phase 1 : Interopérabilité
- [ ] API FHIR R4 conforme (validation serveur public)
- [ ] Échange ADT en HL7 v2 fonctionnel
- [ ] Tests sécurité OWASP passés
- [ ] Infrastructure scalable déployée
- [ ] Documentation technique complète

### Phase 2 : CPOE/CDS
- [ ] 100% prescriptions électroniques
- [ ] IA détecte 95% interactions médicamenteuses connues
- [ ] 0 erreur médicamenteuse liée au système
- [ ] DME enrichi validé par comité médical
- [ ] Formation 100% prescripteurs effectuée

### Phase 3 : LIS + RIS/PACS
- [ ] Transmission automatique résultats labo au DME
- [ ] Stockage et visualisation images DICOM fonctionnels
- [ ] IA détection anomalies >80% précision (prototype)
- [ ] Intégration 2+ automates laboratoire
- [ ] Validation radiologues pour visualiseur

### Phase 4 : Portail + Télémédecine
- [ ] 50% patients cibles inscrits
- [ ] 100 téléconsultations réalisées avec succès
- [ ] Application mobile iOS + Android publiée
- [ ] Conformité réglementaire télémédecine validée
- [ ] Satisfaction patients >4/5

### Phase 5 : BI
- [ ] 30+ KPI temps réel opérationnels
- [ ] Data Warehouse centralisé
- [ ] 3 modèles IA prédictifs en production
- [ ] Tableaux de bord validés par direction
- [ ] ROI BI mesurable (économies >50K€/an)

---

## 📚 Livrables documentaires

### Documentation technique
- [ ] Architecture globale du système
- [ ] Documentation API (OpenAPI/Swagger)
- [ ] Guide développeur
- [ ] Guide d'installation et déploiement
- [ ] Procédures backup/restore
- [ ] Plan de reprise après sinistre (PRA)

### Documentation utilisateur
- [ ] Guide utilisateur par rôle (médecin, infirmier, admin, etc.)
- [ ] Tutoriels vidéo
- [ ] FAQ
- [ ] Guide portail patient
- [ ] Guide application mobile

### Documentation projet
- [ ] Cahier des charges validé
- [ ] Spécifications fonctionnelles détaillées
- [ ] Dossier d'architecture technique (DAT)
- [ ] Plan de tests et résultats
- [ ] Rapport d'audit sécurité
- [ ] Dossier de conformité RGPD

---

## 🎓 Plan de formation

### Formation équipe technique (200h total)

| Formation | Durée | Cible | Période |
|-----------|-------|-------|---------|
| HL7 FHIR avancé | 3 jours | Tous dev | Phase 1 |
| DICOM et imagerie | 2 jours | Dev imagerie | Phase 3 |
| IA/ML en santé | 5 jours | Dev + Data | Phase 2 |
| Sécurité et RGPD | 2 jours | Tous | Phase 1 |
| Méthodologie Agile | 1 jour | Tous | Début |

### Formation utilisateurs finaux (500h total)

| Formation | Durée | Cible | Période |
|-----------|-------|-------|---------|
| DME et CPOE | 4h | Médecins (50) | Phase 2 |
| Prescriptions électroniques | 2h | Médecins (50) | Phase 2 |
| LIS | 3h | Biologistes (10) | Phase 3 |
| RIS/PACS | 3h | Radiologues (15) | Phase 3 |
| Portail et téléconsultation | 2h | Médecins (30) | Phase 4 |
| BI et dashboards | 2h | Managers (20) | Phase 5 |
| Formation générale | 2h | Tous (200) | Toutes phases |

---

## 📞 Gouvernance du projet

### Comité de pilotage (mensuel)
- **Participants:** Direction générale, DSI, Direction médicale, Chef de projet
- **Objectifs:** Validation orientations stratégiques, arbitrages budgétaires
- **Livrables:** Compte-rendu décisions, mise à jour roadmap

### Comité projet (bimensuel)
- **Participants:** Chef de projet, Architecte, Leads techniques, Représentants utilisateurs
- **Objectifs:** Suivi avancement, résolution problèmes, validation livrables
- **Livrables:** Dashboard KPI, rapport avancement, issues bloquantes

### Revues de sprint (toutes les 2 semaines)
- **Participants:** Équipe dev + Product Owner + Utilisateurs clés
- **Objectifs:** Démo fonctionnalités, feedback utilisateurs, planification sprint suivant
- **Livrables:** Incrément logiciel, backlog mis à jour

### Daily Standup (quotidien - 15 min)
- **Participants:** Équipe dev + Scrum Master
- **Format:** Quoi fait hier ? Quoi aujourd'hui ? Blocages ?

---

## 🚀 Quick Start - Première semaine

### Jour 1-2 : Kickoff
- [ ] Réunion lancement officiel projet
- [ ] Présentation équipe et rôles
- [ ] Validation périmètre et roadmap
- [ ] Signature contrats consultants
- [ ] Configuration accès et outils

### Jour 3-5 : Setup technique
- [ ] Création repositories Git
- [ ] Configuration environnements dev
- [ ] Installation outils développement
- [ ] Mise en place CI/CD basique
- [ ] Premier build réussi

### Semaine 2 : Sprint 0
- [ ] Formation Agile/Scrum équipe
- [ ] Définition "Definition of Done"
- [ ] Création backlog initial
- [ ] Estimation premiers user stories
- [ ] Planification Sprint 1

---

## 📝 Conclusion et prochaines étapes

### Validation requise (Semaine actuelle)
1. ✅ **Approbation direction** sur budget et délais
2. ✅ **Validation équipe médicale** sur priorités fonctionnelles
3. ✅ **Accord DSI** sur choix techniques

### Actions immédiates (Semaine prochaine)
1. 🔴 **Recrutement** : Lancer processus recrutement (3 dev + 1 data engineer)
2. 🔴 **Consultants** : Contractualiser expert HL7 FHIR
3. 🔴 **Infrastructure** : Ouvrir comptes cloud (AWS/GCP)
4. 🟡 **Formation** : Inscrire équipe formation HL7 FHIR

### Jalons critiques 2026
- **31 Mars 2026** : Fin Phase 1 - Interopérabilité ✅
- **31 Juillet 2026** : Fin Phase 2 - CPOE/CDS avec IA ✅
- **30 Nov 2026** : Fin Phase 3 - LIS + RIS/PACS ✅
- **31 Mars 2027** : Fin Phase 4 - Portail + Télémédecine ✅

---

**Contact Projet:**  
📧 chef.projet.sih@medkey.com  
📱 +XXX XXX XXX XXX  
🌐 https://projet-sih.medkey.com

---

*Document préparé par l'équipe technique Medkey*  
*Dernière mise à jour : Octobre 2025*  
*Statut : DRAFT - En attente validation*

