import { ReactNode } from "react";
import { Link, useLocation } from "wouter";
import { LayoutDashboard, Package, History, Package2, LogOut, ShieldCheck, Eye } from "lucide-react";
import { cn } from "@/lib/utils";
import { useAuth } from "@/hooks/use-auth";
import { Button } from "@/components/ui/button";

interface LayoutProps {
  children: ReactNode;
}

const navItems = [
  { name: "Dashboard", href: "/", icon: LayoutDashboard },
  { name: "Inventory", href: "/inventory", icon: Package },
  { name: "History", href: "/history", icon: History },
];

export function Layout({ children }: LayoutProps) {
  const [location] = useLocation();
  const { user, logout, isManager } = useAuth();

  return (
    <div className="flex h-screen bg-background">
      {/* Sidebar */}
      <aside className="w-64 flex-shrink-0 border-r border-border bg-sidebar hidden md:flex flex-col">
        <div className="h-16 flex items-center px-6 border-b border-border">
          <div className="flex items-center gap-2.5">
            <div className="bg-primary/10 p-2 rounded-xl">
              <Package2 className="w-6 h-6 text-primary" />
            </div>
            <span className="font-display font-bold text-xl text-foreground">Stockify</span>
          </div>
        </div>

        <nav className="flex-1 px-4 py-6 space-y-2">
          {navItems.map((item) => {
            const isActive = location === item.href;
            return (
              <Link
                key={item.name}
                href={item.href}
                className={cn(
                  "flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all duration-200",
                  isActive
                    ? "bg-primary/10 text-primary"
                    : "text-muted-foreground hover:bg-muted hover:text-foreground"
                )}
              >
                <item.icon className={cn("w-5 h-5", isActive ? "text-primary" : "text-muted-foreground")} />
                {item.name}
              </Link>
            );
          })}
        </nav>

        {/* User info + logout */}
        {user && (
          <div className="p-4 border-t border-border">
            <div className="flex items-center gap-3 px-2 py-2 rounded-xl bg-muted/40 mb-2">
              <div className={cn(
                "p-1.5 rounded-lg",
                isManager ? "bg-primary/10" : "bg-muted"
              )}>
                {isManager
                  ? <ShieldCheck className="w-4 h-4 text-primary" />
                  : <Eye className="w-4 h-4 text-muted-foreground" />
                }
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-foreground truncate capitalize">{user.username}</p>
                <p className="text-xs text-muted-foreground capitalize">{user.role}</p>
              </div>
            </div>
            <Button
              variant="ghost"
              size="sm"
              className="w-full justify-start gap-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl"
              onClick={logout}
            >
              <LogOut className="w-4 h-4" />
              Sign out
            </Button>
          </div>
        )}
      </aside>

      {/* Main Content */}
      <main className="flex-1 flex flex-col overflow-hidden">
        {/* Mobile Header */}
        <header className="h-16 md:hidden border-b border-border bg-background flex items-center justify-between px-4">
          <div className="flex items-center gap-2">
            <Package2 className="w-6 h-6 text-primary" />
            <span className="font-display font-bold text-lg">Stockify</span>
          </div>
          {user && (
            <Button variant="ghost" size="sm" className="gap-1.5 text-muted-foreground" onClick={logout}>
              <LogOut className="w-4 h-4" />
              <span className="text-xs">Sign out</span>
            </Button>
          )}
        </header>

        <div className="flex-1 overflow-auto p-4 md:p-8">
          <div className="max-w-6xl mx-auto w-full">
            {children}
          </div>
        </div>
      </main>
    </div>
  );
}
