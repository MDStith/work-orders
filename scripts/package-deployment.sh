#!/usr/bin/env bash

set -euo pipefail

project_root="$(git rev-parse --show-toplevel)"
cd "$project_root"

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Commit or otherwise resolve working-tree changes before packaging."
    exit 1
fi

if [[ ! -f public/build/manifest.json ]]; then
    echo "Missing frontend build. Run npm run build first."
    exit 1
fi

if [[ -f public/hot ]]; then
    echo "Remove the stale public/hot file before packaging."
    exit 1
fi

if git ls-files | grep -Eq '(^|/)\.env($|\.)' \
    && git ls-files | grep -E '(^|/)\.env($|\.)' \
        | grep -qvE '(^|/)\.env\.example$'; then
    echo "Unexpected tracked environment file. Review before packaging."
    exit 1
fi

commit="$(git rev-parse HEAD)"
short_commit="$(git rev-parse --short HEAD)"
output_dir="$project_root/dist"
archive="$output_dir/work-orders-$short_commit.tar.gz"
staging_dir="$(mktemp -d)"

trap 'rm -rf "$staging_dir"' EXIT

mkdir -p "$output_dir"

git archive HEAD | tar -x -C "$staging_dir"

mkdir -p "$staging_dir/public/build"
cp -a public/build/. "$staging_dir/public/build/"

printf '%s\n' "$commit" > "$staging_dir/REVISION"

tar -czf "$archive" -C "$staging_dir" .

echo "Created: $archive"
echo "Source revision: $commit"