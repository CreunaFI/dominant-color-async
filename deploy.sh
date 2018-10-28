git clone https://github.com/CreunaFI/dominant-color-async-packagist-release.git
rsync -av --progress . /dominant-color-async-packagist-release --exclude dominant-color-async-packagist-release
git config --global user.email "johannes@siipo.la"
git config --global user.name "Johannes Siipola"
git add -A -f
git commit -m "Release $TRAVIS_TAG"
git tag "$TRAVIS_TAG"
git push
