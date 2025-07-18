# GitHub Setup Guide for BD Dynamic Pricing Pro

## Trinn 1: Forberede Pluginen

✅ **Fullført**: Pluginen er nå klar med automatisk oppdateringsfunksjonalitet

## Trinn 2: GitHub Repository Setup

### 2.1 Opprett Repository
1. Gå til GitHub.com og opprett et nytt repository
2. Navn: `bd-dynamic-pricing-pro`
3. Velg Public eller Private (avhengig av dine behov)

### 2.2 Oppdater Plugin Headers
Plugin headers er allerede oppdatert med:
- GitHub Plugin URI: `buenedata/bd-dynamic-pricing-pro`
- Plugin URI: `https://github.com/buenedata/bd-dynamic-pricing-pro`
- Primary Branch: `main`

### 2.3 For Private Repositories
Hvis repositoryet er privat, må du:
1. Opprett en GitHub Personal Access Token
2. Oppdater plugin-initialiseringen med token:
```php
new BD_Dynamic_Pricing_GitHub_Updater(__FILE__, 'buenedata', 'bd-dynamic-pricing-pro', 'ditt-github-token');
```

## Trinn 3: Upload til GitHub

```bash
cd "d:\Programmering\GitHub\bd-dynamic-pricing-pro"
git init
git add .
git commit -m "Initial commit with GitHub updater"
git branch -M main
git remote add origin https://github.com/buenedata/bd-dynamic-pricing-pro.git
git push -u origin main
```

## Trinn 4: Opprett Din Første Release

### Manuell metode:
1. Gå til din GitHub repository
2. Klikk på "Releases" → "Create a new release"
3. Tag version: `v1.2.0`
4. Release title: `Version 1.2.0 - GitHub Updater Integration`
5. Beskriv endringene i release notes (bruk CHANGELOG.md som referanse)
6. Klikk "Publish release"

### Automatisk metode (med GitHub Actions):
```bash
git tag v1.2.0
git push origin v1.2.0
```

## Trinn 5: Test Automatic Updates

1. Installer pluginen på en WordPress-side
2. Opprett en ny release på GitHub (f.eks. v1.1.1)
3. Gå til WordPress Admin → Dashboard → Updates
4. Du skal se din plugin i listen over tilgjengelige oppdateringer

## Fremtidige Releases

### For hver ny versjon:
1. Oppdater versjonsnummeret i `bd-dynamic-pricing-pro.php`
2. Oppdater `CHANGELOG.md` med endringene
3. Commit endringene til GitHub
4. Opprett en ny release med samme versjonsnummer

### Eksempel release-prosess:
```bash
# Endre versjon i bd-dynamic-pricing-pro.php til "1.2.0"
# Oppdater CHANGELOG.md

git add .
git commit -m "Version 1.2.0 - Add new pricing features"
git push origin main
git tag v1.2.0
git push origin v1.2.0
```

## Viktige Notater

- **Versjonsnumre**: Bruk semantisk versjonering (major.minor.patch)
- **Tag format**: Bruk `v` prefix (v1.1.0, v1.2.0, etc.)
- **Testing**: Test alltid oppdateringer på en staging-side først
- **Backup**: Brukere bør ta backup før oppdatering
- **Compatibility**: Sørg for WordPress og WooCommerce-kompatibilitet før release

## Migreringsinformasjon

Denne versjonen har migrert fra `plugin-update-checker` biblioteket til en egendefinert GitHub updater:

### Fjernet:
- `includes/plugin-update-checker/` mappe og alle filer
- Puc_v5p6_Factory avhengighet

### Lagt til:
- `includes/github-updater.php` - Egendefinert updater klasse
- `CHANGELOG.md` - Versjonskontroll
- `GITHUB_SETUP.md` - Oppsettveiledning

## Feilsøking

### Oppdateringer vises ikke:
1. Sjekk at GitHub repository URL er korrekt
2. Verifiser at release er publisert (ikke draft)
3. Kontroller at versjonsnummeret er høyere enn installert versjon
4. Sjekk WordPress error log for GitHub API feil

### Private repository problemer:
1. Sørg for at GitHub token har riktige tillatelser
2. Token må ha 'repo' scope for private repositories

### Migreringsproblemer:
Hvis du opplever problemer etter migreringen fra plugin-update-checker:
1. Deaktiver og reaktiver pluginen
2. Sjekk at `includes/github-updater.php` finnes
3. Kontroller at det ikke finnes konflikter med gamle cache-filer
