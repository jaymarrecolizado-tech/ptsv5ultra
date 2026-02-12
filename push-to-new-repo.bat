@echo off
echo ==========================================
echo Pushing to: jaymarrecolizado-tech/ptsv5ultra
echo ==========================================
echo.

cd /d C:\xampp\htdocs\projects\newPTS

echo 1. Updating remote URL...
git remote set-url origin https://github.com/jaymarrecolizado-tech/ptsv5ultra.git

echo.
echo 2. Checking remote...
git remote -v

echo.
echo 3. Pushing to new repository...
git push -u origin master

echo.
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ==========================================
    echo SUCCESS! Code pushed to:
    echo https://github.com/jaymarrecolizado-tech/ptsv5ultra
    echo ==========================================
) else (
    echo.
    echo ==========================================
    echo ERROR: Push failed!
    echo.
    echo Try this instead:
    echo 1. Go to: https://github.com/jaymarrecolizado-tech/ptsv5ultra
    echo 2. Click "Upload files"
    echo 3. Upload: C:\xampp\htdocs\projects\newPTS-export.zip
    echo ==========================================
)

echo.
pause
