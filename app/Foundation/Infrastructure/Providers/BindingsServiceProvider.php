<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Foundation\Domain\AdminIdentity\Repositories\AdminRepository;
use App\Foundation\Domain\Rbac\Repositories\RoleRepository;
use App\Foundation\Domain\Rbac\Repositories\PermissionRepository;
use App\Foundation\Domain\Product\Repositories\ProductRepository;
use App\Foundation\Domain\CatalogCategory\Repositories\CatalogCategoryRepository;
use App\Foundation\Domain\Brand\Repositories\BrandRepository;
use App\Foundation\Domain\Blog\Repositories\BlogRepository;
use App\Foundation\Domain\BlogCategory\Repositories\BlogCategoryRepository;
use App\Foundation\Domain\Campaign\Repositories\CampaignRepository;
use App\Foundation\Domain\Banner\Repositories\BannerRepository;
use App\Foundation\Domain\Emi\Repositories\EmiRequestRepository;
use App\Foundation\Domain\Emi\Repositories\EmiApplicationRepository;
use App\Foundation\Domain\Emi\Repositories\EmiUserRepository;
use App\Foundation\Domain\Emi\Banks\Nabil\NabilSchema;
use App\Foundation\Domain\Emi\Banks\Sbl\SblSchema;
use App\Foundation\Domain\Emi\Banks\Global\GlobalSchema;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentAdminRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentRoleRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentPermissionRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentCatalogCategoryRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentBrandRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentBlogRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentBlogCategoryRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentCampaignRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentBannerRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmiRequestRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmiApplicationRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmiUserRepository;
use App\Foundation\Infrastructure\Emi\BankSchemaRegistry;
use App\Foundation\Infrastructure\Pdf\Bank\GlobalPdfGenerator;
use App\Foundation\Infrastructure\Pdf\Bank\NabilPdfGenerator;
use App\Foundation\Infrastructure\Pdf\Bank\SiddarthaPdfGenerator;
use App\Foundation\Infrastructure\Pdf\Registry\BankPdfGeneratorRegistry;
use App\Foundation\Infrastructure\Pdf\Renderer\HtmlPassthroughPdfRenderer;
use App\Foundation\Infrastructure\Pdf\Renderer\PdfRenderer;
use App\Foundation\Infrastructure\Auth\Hashing\LaravelPasswordHasher;
use App\Foundation\Shared\Application\Contracts\PasswordHasher;

class BindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // TODO: Bind domain repositories to Eloquent implementations.
        $this->app->bind(AdminRepository::class, EloquentAdminRepository::class);
        $this->app->bind(RoleRepository::class, EloquentRoleRepository::class);
        $this->app->bind(PermissionRepository::class, EloquentPermissionRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(CatalogCategoryRepository::class, EloquentCatalogCategoryRepository::class);
        $this->app->bind(BrandRepository::class, EloquentBrandRepository::class);
        $this->app->bind(BlogRepository::class, EloquentBlogRepository::class);
        $this->app->bind(BlogCategoryRepository::class, EloquentBlogCategoryRepository::class);
        $this->app->bind(CampaignRepository::class, EloquentCampaignRepository::class);
        $this->app->bind(BannerRepository::class, EloquentBannerRepository::class);
        $this->app->bind(EmiRequestRepository::class, EloquentEmiRequestRepository::class);
        $this->app->bind(EmiApplicationRepository::class, EloquentEmiApplicationRepository::class);
        $this->app->bind(EmiUserRepository::class, EloquentEmiUserRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);

        $this->app->bind(PdfRenderer::class, HtmlPassthroughPdfRenderer::class);

        $this->app->singleton(BankSchemaRegistry::class, function (): BankSchemaRegistry {
            return new BankSchemaRegistry([
                new NabilSchema(),
                new SblSchema(),
                new GlobalSchema(),
            ]);
        });

        $this->app->singleton(BankPdfGeneratorRegistry::class, function ($app): BankPdfGeneratorRegistry {
            return new BankPdfGeneratorRegistry([
                new NabilPdfGenerator($app->make(PdfRenderer::class)),
                new SiddarthaPdfGenerator($app->make(PdfRenderer::class)),
                new GlobalPdfGenerator($app->make(PdfRenderer::class)),
            ]);
        });
    }
}
