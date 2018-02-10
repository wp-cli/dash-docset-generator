<?php

namespace DashWpCli;

use Alchemy\Zippy\Zippy;
use Composer\Semver\Comparator as SemverComparator;
use Github\Api\GitData;
use Github\Api\Repo;
use Github\Client as GithubClient;
use Symfony\Component\Finder\Finder;

final class DashWpCli
{
    public static function buildDocs()
    {
        self::downloadDocs();
        self::buildVersions();
    }

    private static function buildVersions()
    {
        /** @var Docset[] $docsets */
        $docsets = [];
        /** @var Finder $versionDirs */
        $versionDirs = (new Finder())
            ->directories()
            ->depth(0)
            ->in(__DIR__ . '/versions');

        foreach ($versionDirs as $versionDir) {
            $version = $versionDir->getBasename();
            $docset = new Docset($version);
            $docset->save(__DIR__ . "/../versions/{$version}");
            $docsets[] = $docset;
        }
        $latest = array_reduce(
            $docsets,
            function (Docset $latest, Docset $current) {
                return SemverComparator::greaterThan(
                    $latest->version,
                    $current->version
                ) ? $latest : $current;
            }, $docsets[0]
        );
        $latest->save(__DIR__ . '/..');
    }

    private static function downloadDocs()
    {
        $gh = new GithubClient();
        /** @var Repo $ghRepo */
        $ghRepo = $gh->api('repo');
        /** @var GitData $ghGitData */
        $ghGitData = $gh->api('gitData');
        $ghWpCliOrg = Constants::GITHUB_WP_CLI_ORG;
        $ghHandbookRepo = Constants::GITHUB_WP_CLI_HANDBOOK_REPO;
        $releases = $ghRepo
            ->releases()
            ->all($ghWpCliOrg, $ghHandbookRepo);

        if ($releases) {
            $versionsDir = __DIR__ . '/versions';

            if (!is_dir($versionsDir)) {
                mkdir($versionsDir);
            }
            foreach ($releases as $release) {
                $tag = $release['tag_name'];

                if (!$release['prerelease'] &&
                    preg_match('/v\d+\.\d+\.\d+/', $tag)
                ) {
                    $hash = substr(
                        $ghGitData
                            ->references()
                            ->show(
                                $ghWpCliOrg,
                                $ghHandbookRepo,
                                "tags/{$tag}"
                            )['object']['sha'],
                        0, 7
                    );
                    $version = str_replace('v', '', $tag);
                    $versionDir = "{$versionsDir}/{$version}";
                    $zippy = Zippy::load();

                    if (!file_exists($versionDir)) {
                        $tarFile = __DIR__ . "/versions/{$version}.tar.gz";

                        file_put_contents(
                            $tarFile,
                            $ghRepo
                                ->contents()
                                ->archive(
                                    $ghWpCliOrg,
                                    $ghHandbookRepo,
                                    'tarball',
                                    "tags/{$tag}"
                                )
                        );
                        $zippy->open($tarFile)->extract($versionsDir);
                        rename(
                            "{$versionsDir}/wp-cli-handbook-{$hash}",
                            "{$versionsDir}/{$version}"
                        );
                        unlink($tarFile);
                    }
                }
            }
        }
    }
}
