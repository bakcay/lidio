<div align="center">  
  <a href="README.md"   >   TR <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/TR.png" alt="TR" height="20" /></a>  
  <a href="README-EN.md"> | EN <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/US.png" alt="EN" height="20" /></a>  
  <a href="README-DE.md"> | DE <img style="padding-top: 8px" src="https://raw.githubusercontent.com/yammadev/flag-icons/master/png/DE.png" alt="DE" height="20" /></a>  
</div>

# Lidio Zahlungsmodul für WHMCS

**Modul aktivieren**:
Platzieren Sie die Moduldateien im WHMCS-Ordner. Sie sollten in das Verzeichnis `modules/gateways/lidio` kopiert werden.

**Aktivierungsschritte**:
1. Gehen Sie zum Bereich **apps / integrations** im WHMCS-Adminpanel.
2. Klicken Sie auf den Tab **Payments** und suchen Sie nach Lidio.
3. Klicken Sie, um Lidio zu aktivieren.

Danach füllen Sie in der Sektion **system settings** > **payment gateways** die folgenden Felder aus:
- Merchant Code
- API Key (Token)
- Merchant Key
- API Passwort

Nach Aktivierung des Moduls funktioniert es in 3 verschiedenen Modi:
1. **3D**: Innerhalb eines Iframes werden die Zahlungen Ihrer Kunden für Rechnungen im WHMCS-Interface angezeigt und die Banktransaktionen im Händlermodus dargestellt.
2. **2D**: Derzeit nicht unterstützt.
3. **Linked**: Leitet den Kunden zur Bezahlung der Rechnung an Lidio-Webseiten weiter. Das System kehrt nicht automatisch zurück.
4. **Hosted**: Leitet den Kunden zur Bezahlung der Rechnung an Lidio-Webseiten weiter. Das System kehrt automatisch zurück, wenn die Zahlung erfolgreich oder fehlgeschlagen ist.

Lidio Webseite: [https://www.lidio.com/](https://www.lidio.com/)
Entwickler Webseite: [https://bunyam.in/](https://bunyam.in/)
Entwickler GitHub Seite: [https://github.com/bakcay](https://github.com/bakcay)
