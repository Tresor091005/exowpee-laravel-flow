# Valeurs par défaut
VERSION ?= patch
MESSAGE ?= "Update package"

# Commande publish avec paramètres
publish:
	@echo "📝 Committing changes..."
	git add .
	git commit -m "$(MESSAGE)" || true
	@echo "🏷️  Creating new version..."
	@npm version $(VERSION) --no-git-tag-version || (echo "npm not found, using manual versioning" && echo "")
	@NEW_VERSION=$$(grep '"version"' composer.json | head -1 | sed 's/.*"version": "\(.*\)".*/\1/' || echo "0.0.1"); \
	echo "New version: $$NEW_VERSION"; \
	git add composer.json; \
	git commit -m "Bump version to $$NEW_VERSION" || true; \
	git tag "v$$NEW_VERSION"; \
	echo "🚀 Pushing to origin..."; \
	git push origin main; \
	git push --tags; \
	echo "✅ Published v$$NEW_VERSION"

# Raccourcis pratiques
publish-patch:
	@$(MAKE) publish VERSION=patch MESSAGE="$(MESSAGE)"

publish-minor:
	@$(MAKE) publish VERSION=minor MESSAGE="$(MESSAGE)"

publish-major:
	@$(MAKE) publish VERSION=major MESSAGE="$(MESSAGE)"

# Version manuelle
publish-version:
	@read -p "Enter version (e.g., 1.2.3): " ver; \
	read -p "Enter commit message: " msg; \
	git add .; \
	git commit -m "$$msg" || true; \
	git tag "v$$ver"; \
	git push origin main; \
	git push --tags; \
	echo "✅ Published v$$ver"