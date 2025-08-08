# BD Dynamic Pricing Pro

En kraftig WordPress plugin for dynamisk prissetting med støtte for kampanjer, rabatter og lisensbasert tilgang.

## Funksjoner

- **Dynamisk prissetting**: Automatisk justering av priser basert på definerte regler
- **Kampanjer og rabatter**: Fleksibel rabattstruktur for ulike scenarioer
- **Lisensbasert tilgang**: Pro-versjon med utvidede funksjoner
- **Automatiske oppdateringer**: Sømløse oppdateringer direkte fra GitHub
- **Admin-grensesnitt**: Intuitivt grensesnitt for regeladministrasjon

## Installasjon

### Automatisk installasjon (anbefalt)
1. Gå til WordPress Admin → Plugins → Legg til ny
2. Søk etter "BD Dynamic Pricing Pro"
3. Klikk "Installer nå" og deretter "Aktiver"

### Manuell installasjon
1. Last ned den nyeste ZIP-filen fra [GitHub Releases](https://github.com/buenedata/bd-dynamic-pricing-pro/releases)
2. Gå til WordPress Admin → Plugins → Legg til ny → Last opp plugin
3. Velg ZIP-filen og klikk "Installer nå"
4. Aktiver pluginen

## Automatiske Oppdateringer

Denne pluginen støtter automatiske oppdateringer via GitHub. Når nye versjoner publiseres, vil de dukke opp i WordPress Admin under Dashboard → Updates.

### Oppdateringssystem
- **GitHub Actions**: Automatisk release-generering ved push til main branch
- **WordPress-native**: Oppdateringsnotifikasjoner i WordPress admin
- **En-klikks oppdatering**: Direkte installasjon fra WordPress admin
- **Versjonshåndtering**: Automatisk changelog-generering

## Versjonsinformasjon

**Nyeste versjon**: 1.5.0

### Endringer i v1.5.0
- Implementert kritiske rettelser for GitHub update system
- Forbedret plugin slug detection (bruker repository name)
- Lagt til API caching med transients (12 timer)
- Forbedret update check logikk med proper validering
- Lagt til omfattende debug logging
- Unikt klassenavn for å unngå konflikter med andre BD plugins
- Oppdatert .gitignore for produksjon

Se `CHANGELOG.md` for fullstendig versjonhistorikk.

## Tekniske detaljer

### Systemkrav
- WordPress 5.0 eller nyere
- PHP 7.4 eller nyere
- Testet opp til WordPress 6.4

### GitHub Update System
Pluginen bruker et avansert oppdateringssystem som kombinerer:
- GitHub Actions for automatisk release-generering
- WordPress sitt innebygde oppdateringssystem
- Sikker nedlasting og installasjon via GitHub API

## Support

For support og spørsmål:
- **Besøk**: [https://buenedata.no](https://buenedata.no)
- **Kjøp Pro-versjon**: [BD Dynamic Pricing Pro](https://buenedata.no/produkter/plugins/bd-dynamic-pricing-pro)
- **GitHub Issues**: [Rapporter problemer](https://github.com/buenedata/bd-dynamic-pricing-pro/issues)

## Utviklet av

**Buene Data**
[https://buenedata.no](https://buenedata.no)

Spesialisert på WordPress plugins og tilpassede løsninger for norske bedrifter.

## Lisens

Dette er en proprietær plugin. Se LICENSE.txt for detaljer.

---

*🤖 Automatisk oppdatert via GitHub Actions*
