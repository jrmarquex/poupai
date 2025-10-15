@echo off
echo ========================================
echo    BACKUP DO BANCO SUPABASE
echo ========================================
echo.
echo Fazendo backup antes de executar migrations...
echo.

node backup_script.js

echo.
echo ========================================
echo    BACKUP CONCLUIDO!
echo ========================================
echo.
echo Verifique a pasta 'backups' para ver os arquivos criados.
echo Se algo der errado, use o script de restauracao.
echo.
pause
