import { useState } from "react";
import { Switch, Route, Router as WouterRouter } from "wouter";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import { Layout } from "@/components/layout";
import Dashboard from "@/pages/dashboard";
import Inventory from "@/pages/inventory";
import History from "@/pages/history";
import LoginPage from "@/pages/login";
import SignupPage from "@/pages/signup";
import NotFound from "@/pages/not-found";
import { AuthCtx, useAuthProvider } from "@/hooks/use-auth";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      staleTime: 1000 * 60 * 5,
      retry: false,
    },
  },
});

type AuthView = "login" | "signup";

function AuthGate() {
  const auth = useAuthProvider();
  const [authView, setAuthView] = useState<AuthView>("login");

  if (auth.isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <div className="flex flex-col items-center gap-3 text-muted-foreground">
          <div className="w-8 h-8 border-2 border-primary/30 border-t-primary rounded-full animate-spin" />
          <span className="text-sm">Loading...</span>
        </div>
      </div>
    );
  }

  if (!auth.user) {
    if (authView === "signup") {
      return (
        <SignupPage
          onSuccess={() => queryClient.invalidateQueries()}
          onLoginClick={() => setAuthView("login")}
        />
      );
    }
    return (
      <LoginPage
        onSuccess={() => queryClient.invalidateQueries()}
        onSignupClick={() => setAuthView("signup")}
      />
    );
  }

  return (
    <AuthCtx.Provider value={auth}>
      <Layout>
        <Switch>
          <Route path="/" component={Dashboard} />
          <Route path="/inventory" component={Inventory} />
          <Route path="/history" component={History} />
          <Route component={NotFound} />
        </Switch>
      </Layout>
    </AuthCtx.Provider>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <WouterRouter base={import.meta.env.BASE_URL.replace(/\/$/, "")}>
          <AuthGate />
        </WouterRouter>
        <Toaster />
      </TooltipProvider>
    </QueryClientProvider>
  );
}

export default App;
