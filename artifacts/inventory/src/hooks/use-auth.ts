import { createContext, useContext } from "react";
import { useQueryClient } from "@tanstack/react-query";
import { useGetMe, useLogout, getGetMeQueryKey } from "@workspace/api-client-react";

export interface AuthUser {
  id: number;
  username: string;
  role: "manager" | "guest";
}

export interface AuthContext {
  user: AuthUser | null;
  isLoading: boolean;
  isManager: boolean;
  logout: () => void;
}

export const AuthCtx = createContext<AuthContext>({
  user: null,
  isLoading: true,
  isManager: false,
  logout: () => {},
});

export function useAuth() {
  return useContext(AuthCtx);
}

export function useAuthProvider(): AuthContext {
  const queryClient = useQueryClient();

  const { data, isLoading } = useGetMe({
    query: {
      retry: false,
      staleTime: 1000 * 60 * 5,
    },
  });

  const logoutMutation = useLogout({
    mutation: {
      onSuccess: () => {
        queryClient.clear();
        window.location.href = import.meta.env.BASE_URL || "/";
      },
    },
  });

  const user = data
    ? { id: data.id, username: data.username, role: data.role as "manager" | "guest" }
    : null;

  return {
    user,
    isLoading,
    isManager: user?.role === "manager",
    logout: () => logoutMutation.mutate({}),
  };
}
