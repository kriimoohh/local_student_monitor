# Import de destinataires via CSV / CSV Recipients Import

## Format du fichier CSV / CSV File Format

Le fichier CSV doit contenir un destinataire par ligne. Les formats suivants sont acceptés :
The CSV file must contain one recipient per line. The following formats are accepted:

- **Email** : `email@example.com`
- **Nom d'utilisateur / Username** : `john.doe`
- **ID utilisateur / User ID** : `12345`

## Exemple / Example

```csv
email@example.com
john.doe@university.edu
student123
45678
jane.smith
student.name@school.org
```

## Utilisation / Usage

1. Créez un fichier CSV avec vos destinataires (un par ligne)
   Create a CSV file with your recipients (one per line)

2. Dans le formulaire de création d'alerte, sélectionnez "Importer depuis un fichier CSV"
   In the alert creation form, select "Import from CSV file"

3. Téléchargez votre fichier CSV
   Upload your CSV file

4. Le système recherchera automatiquement les utilisateurs par email, nom d'utilisateur ou ID
   The system will automatically search for users by email, username, or ID

## Notes importantes / Important Notes

- Les utilisateurs supprimés ou non trouvés seront ignorés
  Deleted or not found users will be ignored

- Les doublons seront automatiquement filtrés
  Duplicates will be automatically filtered

- Le fichier doit être au format CSV (valeurs séparées par des virgules)
  The file must be in CSV format (comma-separated values)

- Les lignes vides sont ignorées
  Empty lines are ignored

## Exemple de fichier / Example File

Un exemple de fichier CSV est disponible dans : `examples/recipients_example.csv`
An example CSV file is available at: `examples/recipients_example.csv`
